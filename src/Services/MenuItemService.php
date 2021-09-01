<?php

namespace Inensus\BulkRegistration\Services;


class MenuItemService
{
    public function createMenuItems()
    {
        $menuItem = [
            'name' =>'Customer Registration',
            'url_slug' =>'/bulk-registration/bulk-registration',
            'md_icon' =>'upload_file'
        ];
        return ['menuItem'=>$menuItem,'subMenuItems'=>[]];
    }
}