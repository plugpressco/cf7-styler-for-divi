<?php
/**
 * Entries REST API – list, get, update status, delete, bulk-delete, delete-by-form, export.
 *
 * @package CF7_Mate\Features\Entries
 * @since 3.0.0
 */

namespace CF7_Mate\Features\Entries;

if (!defined('ABSPATH')) {
    exit;
}

class Entries_API
{
    const REST_NAMESPACE = 'cf7-styler/v1';

    public function __construct()
    {
        require_once __DIR__ . '/print.php';
        new Entry_Print();
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route(self::REST_NAMESPACE, '/entries', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_entries'],
                'permission_callback' => [$this, 'check_permission'],
                'args'                => $this->list_args(),
            ],
        ]);

        register_rest_route(self::REST_NAMESPACE, '/entries/(?P<id>\d+)', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_entry'],
                'permission_callback' => [$this, 'check_permission'],
                'args'                => ['id' => ['validate_callback' => function ($v) { return is_numeric($v); }]],
            ],
            [
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_entry'],
                'permission_callback' => [$this, 'check_permission'],
                'args'                => [
                    'id'     => ['validate_callback' => function ($v) { return is_numeric($v); }],
                    'status' => ['type' => 'string', 'enum' => ['new', 'read', 'trash'], 'sanitize_callback' => 'sanitize_text_field'],
                ],
            ],
            [
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_entry'],
                'permission_callback' => [$this, 'check_permission'],
                'args'                => ['id' => ['validate_callback' => function ($v) { return is_numeric($v); }]],
            ],
        ]);

        register_rest_route(self::REST_NAMESPACE, '/entries/bulk-delete', [
            'methods'             => \WP_REST_Server::DELETABLE,
            'callback'            => [$this, 'bulk_delete'],
            'permission_callback' => [$this, 'check_permission'],
            'args'                => [
                'ids' => [
                    'required' => true,
                    'type'     => 'array',
                    'items'    => ['type' => 'integer'],
                    'sanitize_callback' => function ($v) { return array_map('absint', (array) $v); },
                ],
            ],
        ]);

        register_rest_route(self::REST_NAMESPACE, '/entries/delete-by-form/(?P<form_id>\d+)', [
            'methods'             => \WP_REST_Server::DELETABLE,
            'callback'            => [$this, 'delete_by_form'],
            'permission_callback' => [$this, 'check_permission'],
            'args'                => ['form_id' => ['validate_callback' => function ($v) { return is_numeric($v); }]],
        ]);

        register_rest_route(self::REST_NAMESPACE, '/entries/export', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [$this, 'export_csv'],
            'permission_callback' => [$this, 'check_permission'],
            'args'                => $this->list_args(),
        ]);
    }

    public function check_permission()
    {
        return current_user_can('manage_options');
    }

    private function list_args()
    {
        return [
            'per_page' => ['type' => 'integer', 'default' => 20, 'minimum' => 1, 'maximum' => 100],
            'page'     => ['type' => 'integer', 'default' => 1, 'minimum' => 1],
            'status'   => ['type' => 'string', 'enum' => ['new', 'read', 'trash']],
            'form_id'  => ['type' => 'integer'],
            'search'   => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
        ];
    }

    public function get_entries(\WP_REST_Request $request)
    {
        $args = [
            'post_type'      => Entries_CPT::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => (int) $request->get_param('per_page'),
            'paged'          => (int) $request->get_param('page'),
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [], // phpcs:ignore
        ];

        $status_param = $request->get_param('status');
        if ($status_param === 'trash') {
            $args['meta_query'][] = ['key' => '_cf7m_status', 'value' => ['trash', 'spam'], 'compare' => 'IN'];
        } elseif ($status_param && $status_param !== 'all') {
            $args['meta_query'][] = ['key' => '_cf7m_status', 'value' => $status_param, 'compare' => '='];
        } else {
            // Default / "all": exclude trash so main list shows only new + read
            $args['meta_query'][] = ['key' => '_cf7m_status', 'value' => ['trash', 'spam'], 'compare' => 'NOT IN'];
        }
        if ($request->get_param('form_id')) {
            $args['meta_query'][] = ['key' => '_cf7m_form_id', 'value' => (int) $request->get_param('form_id'), 'compare' => '='];
        }
        if ($request->get_param('search')) {
            $args['s'] = $request->get_param('search');
        }

        $query = new \WP_Query($args);
        $items = [];
        foreach ($query->posts as $post) {
            $items[] = $this->format_entry($post);
        }
        wp_reset_postdata();

        return rest_ensure_response([
            'items'        => $items,
            'total'        => (int) $query->found_posts,
            'pages'        => (int) $query->max_num_pages,
            'current_page' => (int) $args['paged'],
            'per_page'     => (int) $args['posts_per_page'],
        ]);
    }

    public function get_entry(\WP_REST_Request $request)
    {
        $post = get_post((int) $request['id']);
        if (!$post || $post->post_type !== Entries_CPT::POST_TYPE) {
            return new \WP_Error('not_found', __('Entry not found.', 'cf7-styler-for-divi'), ['status' => 404]);
        }
        return rest_ensure_response($this->format_entry($post));
    }

    public function update_entry(\WP_REST_Request $request)
    {
        $post = get_post((int) $request['id']);
        if (!$post || $post->post_type !== Entries_CPT::POST_TYPE) {
            return new \WP_Error('not_found', __('Entry not found.', 'cf7-styler-for-divi'), ['status' => 404]);
        }
        update_post_meta($post->ID, '_cf7m_status', $request->get_param('status'));
        return rest_ensure_response(['success' => true, 'message' => __('Entry updated.', 'cf7-styler-for-divi')]);
    }

    public function delete_entry(\WP_REST_Request $request)
    {
        $id = (int) $request['id'];
        $post = get_post($id);
        if (!$post || $post->post_type !== Entries_CPT::POST_TYPE) {
            return new \WP_Error('not_found', __('Entry not found.', 'cf7-styler-for-divi'), ['status' => 404]);
        }
        if (!wp_delete_post($id, true)) {
            return new \WP_Error('delete_failed', __('Delete failed.', 'cf7-styler-for-divi'), ['status' => 500]);
        }
        return rest_ensure_response(['success' => true, 'message' => __('Entry deleted.', 'cf7-styler-for-divi')]);
    }

    public function bulk_delete(\WP_REST_Request $request)
    {
        $ids = $request->get_param('ids');
        $deleted = 0;
        foreach ($ids as $id) {
            $post = get_post($id);
            if ($post && $post->post_type === Entries_CPT::POST_TYPE && wp_delete_post($id, true)) {
                $deleted++;
            }
        }
        return rest_ensure_response([
            'success' => true,
            'deleted' => $deleted,
            'message' => sprintf(
                /* translators: %d: number */
                _n('%d entry deleted.', '%d entries deleted.', $deleted, 'cf7-styler-for-divi'),
                $deleted
            ),
        ]);
    }

    public function delete_by_form(\WP_REST_Request $request)
    {
        global $wpdb;
        $form_id = (int) $request['form_id'];
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT p.ID FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_cf7m_form_id' AND pm.meta_value = %s
             WHERE p.post_type = %s",
            (string) $form_id,
            Entries_CPT::POST_TYPE
        ));
        $deleted = 0;
        foreach ((array) $ids as $id) {
            if (wp_delete_post((int) $id, true)) {
                $deleted++;
            }
        }
        return rest_ensure_response([
            'success' => true,
            'deleted' => $deleted,
            'message' => sprintf(
                /* translators: %d: number */
                _n('%d entry deleted.', '%d entries deleted.', $deleted, 'cf7-styler-for-divi'),
                $deleted
            ),
        ]);
    }

    public function export_csv(\WP_REST_Request $request)
    {
        $args = [
            'post_type'      => Entries_CPT::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => min(500, (int) $request->get_param('per_page') ?: 500),
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [],
        ];
        $status_param = $request->get_param('status');
        if ($status_param === 'trash') {
            $args['meta_query'][] = ['key' => '_cf7m_status', 'value' => ['trash', 'spam'], 'compare' => 'IN'];
        } elseif ($status_param && $status_param !== 'all') {
            $args['meta_query'][] = ['key' => '_cf7m_status', 'value' => $status_param, 'compare' => '='];
        } else {
            $args['meta_query'][] = ['key' => '_cf7m_status', 'value' => ['trash', 'spam'], 'compare' => 'NOT IN'];
        }
        if ($request->get_param('form_id')) {
            $args['meta_query'][] = ['key' => '_cf7m_form_id', 'value' => (int) $request->get_param('form_id')];
        }
        if ($request->get_param('search')) {
            $args['s'] = $request->get_param('search');
        }
        $query = new \WP_Query($args);
        if (!$query->have_posts()) {
            return new \WP_Error('no_entries', __('No entries to export.', 'cf7-styler-for-divi'), ['status' => 404]);
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=cf7-mate-entries-' . gmdate('Y-m-d') . '.csv');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['ID', 'Form ID', 'Form Title', 'Status', 'Created', 'IP', 'Data (JSON)'], ',', '"', '\\');

        foreach ($query->posts as $post) {
            $e = $this->format_entry($post);
            fputcsv($out, [
                $e['id'],
                $e['form_id'],
                $e['form_title'],
                $e['status'],
                $e['created'],
                $e['ip'],
                wp_json_encode($e['data'], JSON_UNESCAPED_UNICODE),
            ], ',', '"', '\\');
        }
        wp_reset_postdata();
        fclose($out); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- writing to php://output stream
        exit;
    }

    private function format_entry($post)
    {
        $form_id   = (int) get_post_meta($post->ID, '_cf7m_form_id', true);
        $form_title = (string) get_post_meta($post->ID, '_cf7m_form_title', true);
        $status    = (string) get_post_meta($post->ID, '_cf7m_status', true);
        $data_raw  = get_post_meta($post->ID, '_cf7m_data', true);
        $created   = (string) get_post_meta($post->ID, '_cf7m_created', true);
        $ip        = (string) get_post_meta($post->ID, '_cf7m_ip', true);
        $ua        = (string) get_post_meta($post->ID, '_cf7m_ua', true);

        if (!$created) {
            $created = $post->post_date;
        }
        $data = is_string($data_raw) ? json_decode($data_raw, true) : [];
        if (!is_array($data)) {
            $data = [];
        }
        // Backward compat: treat legacy 'spam' as 'trash'.
        if ($status === 'spam') {
            $status = 'trash';
        }

        return [
            'id'          => $post->ID,
            'form_id'     => $form_id,
            'form_title'  => $form_title,
            'form_title_with_id' => $form_title ? $form_title . ' (' . $form_id . ')' : (string) $form_id,
            'status'      => $status ?: 'new',
            'data'        => $data,
            'created'     => $created,
            'ip'          => $ip,
            'user_agent'  => $ua,
        ];
    }
}
