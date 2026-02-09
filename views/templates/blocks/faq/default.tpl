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

{* FAQ Rich Snippet - JSON-LD Structured Data *}
{if $block.states|count > 0}
<script type="application/ld+json">
{literal}{{/literal}
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {foreach from=$block.states item='faq' name='faqloop'}
    {literal}{{/literal}
      "@type": "Question",
      "name": "{$faq.question|strip_tags|trim|escape:'javascript'}",
      "acceptedAnswer": {literal}{{/literal}
        "@type": "Answer",
        "text": "{$faq.answer|strip_tags|trim|escape:'javascript'}"
      {literal}}{/literal}
    {literal}}{/literal}{if !$smarty.foreach.faqloop.last},{/if}

    {/foreach}
  ]
{literal}}{/literal}
</script>
{/if}
