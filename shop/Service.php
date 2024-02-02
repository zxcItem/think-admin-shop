<?php

declare (strict_types=1);

namespace app\shop;

use app\shop\command\Clear;
use think\admin\Plugin;

/**
 * 组件注册服务
 * @class Service
 * @package app\shop
 */
class Service extends Plugin
{
    /**
     * 定义插件名称
     * @var string
     */
    protected $appName = '商城管理';

    /**
     * 定义安装包名
     * @var string
     */
    protected $package = 'xiaochao/think-admin-shop';

    /**
     * 插件服务注册
     * @return void
     */
    public function register(): void
    {
        $this->commands([Clear::class]);
    }

    /**
     * 用户模块菜单配置
     * @return array[]
     */
    public static function menu(): array
    {
        // 设置插件菜单
        return [
            [
                'name' => '商城管理',
                'subs' => [
                    [
                        'name' => '商城管理',
                        'subs' => [
                            ['name' => '商城参数管理', 'icon' => 'layui-icon layui-icon-set', 'node' => "shop/base.config/index"],
                            ['name' => '邀请海报设置', 'icon' => 'layui-icon layui-icon-cols', 'node' => 'shop/base.poster/cropper'],
                            ['name' => '商品数据管理', 'icon' => 'layui-icon layui-icon-star', 'node' => 'shop/shop.goods/index'],
                            ['name' => '订单数据管理', 'icon' => 'layui-icon layui-icon-template', 'node' => 'shop/shop.order/index'],
                            ['name' => '订单发货管理', 'icon' => 'layui-icon layui-icon-transfer', 'node' => 'shop/shop.send/index'],
                            ['name' => '快递公司管理', 'icon' => 'layui-icon layui-icon-website', 'node' => 'shop/base.express.company/index'],
                            ['name' => '邮费模板管理', 'icon' => 'layui-icon layui-icon-template-1', 'node' => 'shop/base.express.template/index'],
                        ],
                    ],
                ],
            ]
        ];
    }
}