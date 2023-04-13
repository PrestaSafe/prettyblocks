<button id="toggle-prettyblocks"></button>
<div id="modal-prettyblocks">
    <div class="header">
        <span>PrettyBlocks</span>
        <div class="close-pb">
        </div>
    </div>
    <div class="wrapper">
        <ul>
            <div class="d-inline-block">
                <small>Rechercher dans les </small>
                <select id="prettyblocks-change-search">
                    <option value="product">Produits</option>
                    <option value="category">Categories</option>
                    <option value="cms">Pages CMS</option>
                </select>
                <input type="text" placeholder="Rechercher.." id="prettyblocks-search-products">
                <img width="30" id="prettyblocks-delete-search" src="{$prettyblocks.imgDir}delete.png" />
            </div>
            <div id="loader"><img src="{$prettyblocks.imgDir}load.gif" width="64"></div>
            <div id="prettyblocks-search-results"></div>
        </ul>
    </div>
</div>
