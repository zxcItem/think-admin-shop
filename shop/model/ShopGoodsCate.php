<?php

namespace app\shop\model;

use app\account\model\Abs;
use think\admin\extend\DataExtend;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 商品分类模型
 */
class ShopGoodsCate extends Abs
{
    /**
     * 获取上级可用选项
     * @param int $max 最大级别
     * @param array $data 表单数据
     * @param array $parent 上级分类
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getParentData(int $max, array &$data, array $parent = []): array
    {
        $items = static::mk()->where(['deleted' => 0])->order('sort desc,id asc')->select()->toArray();
        $cates = DataExtend::arr2table(empty($parent) ? $items : array_merge([$parent], $items));
        if (isset($data['id'])) foreach ($cates as $cate) if ($cate['id'] === $data['id']) $data = $cate;
        foreach ($cates as $key => $cate) {
            $isSelf = isset($data['spt']) && isset($data['spc']) && $data['spt'] <= $cate['spt'] && $data['spc'] > 0;
            if ($cate['spt'] >= $max || $isSelf) unset($cates[$key]);
        }
        return $cates;
    }

    /**
     * 获取数据树
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function treeData(): array
    {
        $query = static::mk()->where(['status' => 1, 'deleted' => 0])->order('sort desc,id desc');
        return DataExtend::arr2tree($query->withoutField('sort,status,deleted,create_at')->select()->toArray());
    }

    /**
     * 获取列表数据
     * @param bool $simple 仅子级别
     * @return array
     */
    public static function items(bool $simple = false): array
    {
        $query = static::mk()->where(['status' => 1, 'deleted' => 0])->order('sort desc,id asc');
        $cates = array_column(DataExtend::arr2table($query->column('id,pid,name', 'id')), null, 'id');
        foreach ($cates as $cate) isset($cates[$cate['pid']]) && $cates[$cate['id']]['parent'] =& $cates[$cate['pid']];
        foreach ($cates as $key => $cate) {
            $id = $cate['id'];
            $cates[$id]['ids'][] = $cate['id'];
            $cates[$id]['names'][] = $cate['name'];
            while (isset($cate['parent']) && ($cate = $cate['parent'])) {
                $cates[$id]['ids'][] = $cate['id'];
                $cates[$id]['names'][] = $cate['name'];
            }
            $cates[$id]['ids'] = array_reverse($cates[$id]['ids']);
            $cates[$id]['names'] = array_reverse($cates[$id]['names']);
            if (isset($pky) && $simple && in_array($cates[$pky]['name'], $cates[$id]['names'])) {
                unset($cates[$pky]);
            }
            $pky = $key;
        }
        foreach ($cates as &$cate) {
            unset($cate['sps'], $cate['parent']);
        }
        return array_values($cates);
    }
}