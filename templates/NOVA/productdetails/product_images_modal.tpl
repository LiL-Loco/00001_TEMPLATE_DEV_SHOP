{block name='productdetails-product-images-modal'}
<div class="modal modal-fullview fade" id="productImagesModal" tabindex="-1" role="dialog" aria-label="{lang section='aria' key='imageZoom'}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            {block name='productdetails-product-images-modal-header'}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{lang section='aria' key='close'}">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            {/block}
            {block name='productdetails-product-images-modal-body'}
                <div class="modal-body">
                    {foreach $images as $image}
                        {block name='productdetails-product-images-modal-image'}
                            <div class="square square-image">
                                <div class="inner">
                                    {image alt=$image->cAltAttribut
                                        class="product-image"
                                        fluid=true
                                        lazy=true
                                        webp=true
                                        width=$image->imageSizes->lg->size->width
                                        height=$image->imageSizes->lg->size->height
                                        src="{$Artikel->Bilder[0]->cURLNormal}"
                                        srcset="
                                            {$image->cURLMini} {$image->imageSizes->xs->size->width}w,
                                            {$image->cURLKlein} {$image->imageSizes->sm->size->width}w,
                                            {$image->cURLNormal} {$image->imageSizes->md->size->width}w,
                                            {$image->cURLGross} {$image->imageSizes->lg->size->width}w"
                                        sizes="auto"
                                        data=["list"=>"{$image->galleryJSON|escape:"html"}", "index"=>$image@index]
                                    }
                                </div>
                            </div>
                        {/block}
                    {/foreach}
                </div>
            {/block}
        </div>
    </div>
</div>
{/block}
