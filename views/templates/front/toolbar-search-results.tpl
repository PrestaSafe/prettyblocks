{foreach $results as $result}
    <a class="search-results d-inline-block cursor-pointer p-2" data-id-product="{$result.id}" href="{$result.link}">
        <img width="32" src="{$result.img}" class="mr-2"/> {$result.text}
    </a>
{/foreach}
