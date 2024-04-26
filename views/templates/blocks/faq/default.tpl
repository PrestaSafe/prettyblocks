{* <div class="faq-container">
    <p class="title-center">{$block.settings.title}</p>
    {foreach from=$block.states item='faq'}
    <div class="faq">
        <button class="faq-question">{$faq.question nofilter}</button>
        <p class="faq-answer">{$faq.answer nofilter}</p>
    </div>
    {/foreach}
</div> *}

<div class="{if $block.settings.default.container}container{else}_force-full{/if} pd-m prettyblocks-faq">
  <p class="h2 title-center">{$block.settings.title}</p>
  <div class="accordion">
    {foreach from=$block.states item='faq'}
    <div class="accordion-item">
      <button id="accordion-button-1" aria-expanded="false"><span class="accordion-title">{$faq.question nofilter}</span><span class="icon" aria-hidden="true"></span></button>
      <div class="accordion-content">
        {$faq.answer nofilter}
      </div>
    </div>
    {/foreach}
   
  </div>
</div>



