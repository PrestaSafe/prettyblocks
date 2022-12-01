<?php 
use PrestaShop\PrestaShop\Adapter\Presenter\PresenterInterface;
class BlockPresenter implements PresenterInterface
{
    public function present($block)
    {
        
        $present = [
            'settings' => $block['settings'],
            'states' => $block['states'],
            'extra' => $block['extra'],
            'instance_id' => $block['instance_id'],
            'id_prettyblocks' => $block['id_prettyblocks'],
            'templateSelected' => $block['templateSelected'],
            'templates' => $block['templates']
        ];

        return $present;
    }
}