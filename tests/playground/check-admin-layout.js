async page => {
  const checks=[];
  const assert=(value,message)=>{if(!value)throw new Error(message);checks.push(message);};
  const base=new URL(page.url()).origin;
  await page.goto(base+'/wp-admin/admin.php?page=cf7-mate');
  await page.getByRole('heading',{name:'Features',exact:true}).waitFor();
  const setup=await page.evaluate(async()=>({nonce:dcsCF7Styler.nonce,features:(await wp.apiFetch({path:'/cf7-styler/v1/settings/features'})).features}));
  try {
    assert(await page.locator('.cf7m-content').evaluate(el=>parseFloat(getComputedStyle(el).maxWidth)<=1040),'Admin content uses a compact container');
    const nav=page.getByRole('navigation',{name:'CF7 Mate navigation'});
    assert(await nav.getByRole('link').count()===5 && await page.locator('.cf7m-page-sidebar').count()===0,'All admin pages share one navigation without a promotional sidebar');
    const search=page.getByRole('searchbox',{name:'Search features'});
    await search.fill('webhook');
    assert(await page.getByRole('switch').count()===1 && await page.getByRole('switch',{name:'Webhooks',exact:true}).count()===1,'Feature search narrows the visible controls');
    await search.fill('no-matching-feature-fixture');
    await page.getByRole('heading',{name:'No features found',exact:true}).waitFor();
    await page.getByRole('button',{name:'Clear filters',exact:true}).click();
    await page.getByRole('button',{name:'Disabled',exact:true}).click();
    assert((await page.getByRole('switch').evaluateAll(items=>items.every(item=>item.getAttribute('aria-checked')==='false'))),'Disabled filter only shows disabled features');
    await page.getByRole('button',{name:'Enabled',exact:true}).click();
    assert((await page.getByRole('switch').evaluateAll(items=>items.every(item=>item.getAttribute('aria-checked')==='true'))),'Enabled filter only shows enabled features');
    await page.getByRole('button',{name:'All',exact:true}).click();
    const icon=page.getByRole('switch',{name:'Icon',exact:true});
    const previous=await icon.getAttribute('aria-checked');
    const save=page.waitForResponse(r=>r.url().includes('/settings/features')&&r.request().method()==='POST');
    await icon.click();
    assert((await save).ok(),'A feature toggle saves through the real REST endpoint');
    await page.getByRole('status').filter({hasText:'Settings saved.'}).waitFor();
    const current=await icon.getAttribute('aria-checked');
    assert(current!==previous,'The saved feature toggle reflects its new state');
    await page.route('**/settings/features*',route=>route.request().method()==='POST'?route.fulfill({status:500,contentType:'application/json',body:JSON.stringify({code:'fixture_failure',message:'Fixture save failure'})}):route.continue());
    await icon.click();
    await page.getByRole('alert').filter({hasText:'Could not save'}).waitFor();
    assert(await icon.getAttribute('aria-checked')===current,'Failed saves revert the toggle and show an error');
    await page.unrouteAll({behavior:'wait'});
    await page.route('**/settings/features*',route=>route.request().method()==='GET'?route.fulfill({status:500,contentType:'application/json',body:JSON.stringify({code:'fixture_failure',message:'Fixture load failure'})}):route.continue());
    await page.reload();
    await page.getByRole('heading',{name:'Could not load settings',exact:true}).waitFor();
    assert(await page.getByRole('switch').count()===0,'A load failure cannot show misleading disabled feature controls');
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
      await page.setViewportSize({width:1440,height:1000});
      await page.screenshot({path:'output/playwright/redesign-'+name.toLowerCase().replace(' ','-')+'.png',fullPage:true});
      for(const width of [768,390]) {
        await page.setViewportSize({width,height:900});
        assert(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),name+' fits '+width+'px without page overflow');
      }
      await page.screenshot({path:'output/playwright/redesign-'+name.toLowerCase().replace(' ','-')+'-mobile.png',fullPage:true});
      await page.setViewportSize({width:1440,height:1000});
    }
  } finally {
    await page.unrouteAll({behavior:'wait'});
    await page.request.post(base+'/wp-json/cf7-styler/v1/settings/features',{data:{features:setup.features},headers:{'X-WP-Nonce':setup.nonce}});
    await page.setViewportSize({width:1440,height:1000});
  }
  return checks;
}
