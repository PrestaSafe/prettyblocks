{*
* 2007-2018 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2018 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*}


<div class="row">
  <div class="col-md-2">
    <!-- Nav tabs -->
    <ul class="nav nav-pills nav-stacked" id="tab-custom-form" role="tablist">
    {foreach from=$forms_builder.forms item=form name=loopFormTab}
      <li class="nav-item {if $smarty.foreach.loopFormTab.first}active{/if}">
        
        <a class="nav-link {if $smarty.foreach.loopFormTab.first}active{/if}" id="tab-{$form.id}" data-toggle="tab" href="#home-{$form.id}" role="tab" aria-controls="home" aria-selected="true">
        {if str_starts_with($form.id,'cz_') || str_starts_with($form.id,'design')}
          <img src="{Tools::getShopDomainSsl(true)}/modules/prettyblocks/logo.png" width="25px">
        {/if}
        {if isset($form.tabName)} 
          {$form.tabName|capitalize}
        {else}
          {$form.id|capitalize}
        {/if}
        </a>
      </li>
    {/foreach}
    {foreach from=$wrappers item=wrap}
      <li class="nav-item">
      <a class="nav-link" id="tab-{$wrap.id}" data-toggle="tab" href="#home-{$wrap.id}" role="tab" aria-controls="home" aria-selected="true">
        <img src="{Tools::getShopDomainSsl(true)}/modules/{$wrap.module_to_wrap}/logo.png" width="25px">
        {$wrap.tab_name}
      </a>
      </li>
    {/foreach}
    </ul>
  </div> <!-- end col md 2 -->

  <div class="col-md-10">
    
    <!-- Tab panes -->
    <div class="tab-content tab-custom-form">
    {foreach from=$forms_builder.forms item=form name=loopForm}
      <div class="tab-pane {if $smarty.foreach.loopForm.first}active{/if}" id="home-{$form.id}" role="tabpanel" aria-labelledby="home-{$form.id}">
        {$form.render}
      </div>
      {/foreach}
      {foreach from=$wrappers item=wrap name=loopWrap}
        <div class="tab-pane" id="home-{$wrap.id}" role="tabpanel" aria-labelledby="home-{$wrap.id}">
          <iframe id="iframe-test" style="visibility:hidden" src="{$link->getAdminLink('AdminModules')}&configure={$wrap.module_to_wrap}" width="100%" height="1800px" frameborder="0"></iframe>
        </div>
      {/foreach}
    </div>

    
</div>



<style>
    #tab-custom-form{
      background: white;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.0/dist/js.cookie.min.js"></script>
<script>
  let myiFrame = document.getElementById("iframe-test");
  myiFrame.contentWindow.addEventListener('DOMContentLoaded', (event) => {
      console.log('LOADED')
      myiFrame.style.visibility = 'visible';
      let form =  myiFrame.contentDocument.getElementById('configuration_form')
      foo()
  })

 function foo()
  {
    myiFrame.contentDocument.getElementById('configuration_form').addEventListener('submit', (event) => {
      event.preventDefault()
      alert('SUBMIT')
    })

  }
  function Handler(e) {
    let iFrame = e.target
    
  
    let doc = iFrame.contentDocument;
    let windowIframe = iFrame.contentWindow
    
    iFrame.style.opacity = 0.4;
    iFrame.style.display = 'none';
    let body = doc.body.innerHTML
    var scripts = '<link rel="stylesheet" href="../modules/prettyblocks/views/css/css_for_iframe.css">' 
    var myscript = doc.createElement('script');
    myscript.type = 'text/javascript';
    myscript.src = '../modules/prettyblocks/views/js/js_for_iframe.js'; // replace this with your SCRIPT
    doc.head.appendChild(myscript);


    doc.head.innerHTML =  doc.head.innerHTML + scripts;
    doc.body.innerHTML = '<div class="bootstrap"><div class="text-center d-block w-100 p-5"><span class="material-icons rotating">refresh</span></div></div>'
    setTimeout(() => { 
      doc.body.innerHTML = body
      myiFrame.style.opacity = 1;
    }, 750)
    iFrame.style.display = 'block';   
}

myiFrame.addEventListener('load',Handler)



  

  $(function(){

    if(Cookies.get('active_tab'))
    {
      let tab = Cookies.get('active_tab');
      $('#tab-custom-form li a[href="'+tab+'"]').click();
    }else{
      $('#tab-custom-form li a').first().click();
    }
    $('#tab-custom-form li a').click(function(){
      var href = $(this).attr('href');
      $('.tab-custom-form .tab-pane.active').removeClass('active');
      $(href).addClass('active');
      Cookies.set('active_tab', href);
    });

  })
</script>