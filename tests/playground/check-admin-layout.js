async page => {
  const checks=[];
  const assert=(value,message)=>{if(!value)throw new Error(message);checks.push(message);};
  const base=new URL(page.url()).origin;
  const chrome=async()=>page.evaluate(()=>['#wpadminbar','#adminmenuwrap','#wpfooter'].map(selector=>{const node=document.querySelector(selector);if(!node)return [selector,null];const style=getComputedStyle(node);return [selector,['font-family','font-size','color','background-color','padding-left','padding-right','border-top-width','width'].map(key=>style.getPropertyValue(key)).join('|')];}));
  await page.goto(base+'/wp-admin/index.php');
  const dashboardChrome=await chrome();
  await page.goto(base+'/wp-admin/admin.php?page=cf7-mate');
  await page.getByRole('heading',{name:'Features',exact:true}).waitFor();
  const setup=await page.evaluate(async()=>({nonce:dcsCF7Styler.nonce,features:(await wp.apiFetch({path:'/cf7-styler/v1/settings/features'})).features}));
  try {
    assert(JSON.stringify(await chrome())===JSON.stringify(dashboardChrome),'Admin styles leave the WordPress admin bar, menu and footer unchanged');
    assert(await page.locator('#toplevel_page_cf7-mate').count()===0 && await page.locator('[id^="toplevel_page_wpcf7"] a[href="admin.php?page=cf7-mate"]').count()===1,'CF7 Mate is one item under the Contact menu');
    const nav=page.getByRole('navigation',{name:'CF7 Mate navigation'});
    assert(await nav.getByRole('link').count()===5 && await page.locator('.cf7m-page-sidebar').count()===0,'All admin pages share one navigation without a promotional sidebar');
    assert(await page.locator('.cf7m-content').evaluate(el=>parseFloat(getComputedStyle(el).paddingLeft)===20),'Content uses WordPress-like 20px gutters');
    assert(await page.locator('.cf7m-wrap header img[src*="cf7-mate-mark.png"]').count()===1 && await page.locator('.cf7m-wrap img').count()===1,'The header shows the app icon and no other logo');
    await page.getByText('Help',{exact:true}).click();
    assert(await page.getByRole('button',{name:/^Sounds/}).getAttribute('aria-pressed')==='false','Sounds start off');
    await page.getByText('Help',{exact:true}).click();
    const search=page.getByRole('searchbox',{name:'Search features'});
    await search.fill('webhook');
    assert(await page.getByRole('checkbox').count()===1 && await page.getByRole('checkbox',{name:'Webhooks',exact:true}).count()===1,'Feature search narrows the visible checkboxes');
    await search.fill('no-matching-feature-fixture');
    await page.getByText('No features match.',{exact:true}).waitFor();
    await page.getByRole('button',{name:'Clear search',exact:true}).click();
    assert(await page.getByRole('checkbox').count()>10,'Clearing the search shows every feature again');
    const icon=page.getByRole('checkbox',{name:'Icon',exact:true});
    const previous=await icon.isChecked();
    const save=page.waitForResponse(r=>r.url().includes('/settings/features')&&r.request().method()==='POST');
    await page.locator('label').filter({has:icon}).click();
    assert((await save).ok(),'A feature checkbox saves through the real REST endpoint');
    await page.getByRole('status').filter({hasText:'Saved'}).waitFor();
    const current=await icon.isChecked();
    assert(current!==previous,'The saved checkbox reflects its new state');
    await page.route('**/settings/features*',route=>route.request().method()==='POST'?route.fulfill({status:500,contentType:'application/json',body:JSON.stringify({code:'fixture_failure',message:'Fixture save failure'})}):route.continue());
    await page.locator('label').filter({has:icon}).click();
    await page.getByRole('alert').filter({hasText:'Could not save'}).waitFor();
    assert(await icon.isChecked()===current,'Failed saves revert the checkbox and show an error');
    await page.unrouteAll({behavior:'wait'});
    await page.route('**/settings/features*',route=>route.request().method()==='GET'?route.fulfill({status:500,contentType:'application/json',body:JSON.stringify({code:'fixture_failure',message:'Fixture load failure'})}):route.continue());
    await page.reload();
    await page.getByRole('heading',{name:'Could not load settings',exact:true}).waitFor();
    assert(await page.getByRole('checkbox').count()===0,'A load failure cannot show misleading feature controls');
    await page.unrouteAll({behavior:'wait'});
    await page.getByRole('button',{name:'Try again',exact:true}).click();
    await page.getByRole('heading',{name:'Features',exact:true}).waitFor();
    assert(true,'Settings loading can be retried');
    for(const name of ['Features','Tools','Saddle','Responses','Analytics']) {
      await page.getByRole('navigation',{name:'CF7 Mate navigation'}).getByRole('link',{name,exact:true}).click();
      await page.getByRole('heading',{name,exact:true}).waitFor();
      if(name==='Responses')await page.getByRole('link',{name:'View',exact:true}).first().waitFor();
      if(name==='Analytics')await page.getByText('Performance by form',{exact:true}).waitFor();
      if(name==='Saddle')await page.getByRole('heading',{name:'Quick MCP setup',exact:true}).waitFor();
      if(name==='Tools')await page.getByRole('combobox',{name:'Provider',exact:true}).waitFor();
      if(name!=='Features')assert(await page.locator('[id^="toplevel_page_wpcf7"]').evaluate(el=>el.classList.contains('wp-has-current-submenu'))&&await page.locator('[id^="toplevel_page_wpcf7"] a[href="admin.php?page=cf7-mate"]').getAttribute('aria-current')==='page',name+' highlights Contact > CF7 Mate');
      await page.setViewportSize({width:1440,height:1000});
      await page.screenshot({path:'output/playwright/refresh-'+name.toLowerCase()+'.png',fullPage:true});
      for(const width of [768,390]) {
        await page.setViewportSize({width,height:900});
        assert(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),name+' fits '+width+'px without page overflow');
      }
      await page.screenshot({path:'output/playwright/refresh-'+name.toLowerCase()+'-mobile.png',fullPage:true});
      await page.setViewportSize({width:1440,height:1000});
    }
  } finally {
    await page.unrouteAll({behavior:'wait'});
    await page.request.post(base+'/wp-json/cf7-styler/v1/settings/features',{data:{features:setup.features},headers:{'X-WP-Nonce':setup.nonce}});
    await page.setViewportSize({width:1440,height:1000});
  }
  return checks;
}
