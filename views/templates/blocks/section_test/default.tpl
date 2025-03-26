{$section nofilter}

{foreach from=$elements item=element}
    {$element->render() nofilter}
{/foreach}