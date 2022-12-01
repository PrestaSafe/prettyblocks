
<div class="container flex">
    <h2 class="h3 text-center pb-3">Blog</h2>
    <div class="row">
        {foreach from=$posts_wp_blog item=post name=loopWpPosts}
            <article class="col-lg col-sm-6 product-miniature js-product-miniature thumbnail-container card product-card h-100 reviews-loaded">
            {$thumbnail = $post._embedded["wp:featuredmedia"][0]["media_details"]["sizes"]["full"]}
                <a class="card-img-top d-block overflow-hidden rounded-0 text-center"
                    href="{$post.link}">
                    <img src="{$thumbnail.source_url}"
                        loading="lazy" alt="{$post.title.rendered|strip_tags}" style="height: 200px !important; object-fit: cover">
                </a>
                <div class="card-body d-flex flex-column pl-0 pr-0 pr-2 py-2  mt-1">
                <h2 class="product-title font-size-sm"><a
                href="{$post.link}">{$post.title.rendered|truncate:80 nofilter}</a></h2>
                <a class="btn btn-primary pb-1 pt-1"
                
                    href="{$post.link}">{l s='Read post' mod='prettyblocks'}</a>
                </div>
            </article>
        {/foreach}
    </div>
</div>

