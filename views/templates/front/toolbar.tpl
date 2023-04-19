<button id="toggle-prettyblocks"></button>
<div id="modal-prettyblocks">
    <div class="header">
        <select id="prettyblocks-change-search">
            <option value="product">Produits</option>
            <option value="category">Categories</option>
            <option value="cms">Pages CMS</option>
        </select>
        <div class="close-pb">
        </div>
    </div>
    <div class="wrapper">
        <div>
            <div class="placeholder-wrapper">
                <small>Rechercher dans les <span class="placeholder-value">produits</span></small>
            </div>
            <div class="search-wrapper">
                <input type="text" placeholder="Rechercher.." id="prettyblocks-search-products">
                <img width="30" id="prettyblocks-delete-search" src="{$prettyblocks.imgDir}nav-cross.svg"/>
            </div>
        </div>
        <div id="loader"><img src="{$prettyblocks.imgDir}loading.svg" width="64"></div>
        <div id="prettyblocks-search-results"></div>
    </div>
</div>
