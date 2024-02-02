<?php


declare (strict_types=1);

namespace app\shop\command;

use app\shop\model\ShopOrder;
use app\shop\model\ShopOrderItem;
use app\shop\service\UserOrder;
use think\admin\Command;
use think\admin\Exception;
use think\console\Input;
use think\console\Output;

/**
 * 商城订单自动清理
 * @class Clear
 * @package app\shop\command
 */
class Clear extends Command
{
    protected function configure()
    {
        $this->setName('shop:clear');
        $this->setDescription('清理商城订单数据');
    }

    /**
     * 业务指令执行
     * @param Input $input
     * @param Output $output
     * @return void
     * @throws Exception
     */
    protected function execute(Input $input, Output $output)
    {
        $this->_autoCancelOrder();
        $this->_autoRemoveOrder();
    }

    /**
     * 取消30分钟未支付订单
     * @return void
     * @throws Exception
     */
    private function _autoCancelOrder()
    {
        try {
            $where = [['status', '<', 3], ['create_time', '<', date('Y-m-d H:i:s', strtotime('-30 minutes'))]];
            [$count, $total] = [0, ($items = ShopOrder::mk()->where($where)->select())->count()];
            $items->map(function (ShopOrder $order) use ($total, &$count) {
                if ($order->payment()->findOrEmpty()->isExists()) {
                    $this->queue->message($total, ++$count, "订单 {$order->getAttr('order_no')} 存在支付记录");
                } else {
                    $this->queue->message($total, ++$count, "开始取消未支付的订单 {$order->getAttr('order_no')}");
                    $order->save(['status' => 0, 'cancel_status' => 1, 'cancel_time' => date('Y-m-d H:i:s'), 'cancel_remark' => '自动取消30分钟未完成支付']);
                    UserOrder::stock($order->getAttr('order_no'));
                    $this->queue->message($total, $count, "完成取消未支付的订单 {$order->getAttr('order_no')}", 1);
                }
            });
        } catch (\Exception $exception) {
            $this->setQueueError($exception->getMessage());
        }
    }

    /**
     * 清理已取消的订单
     * @return void
     * @throws Exception
     */
    private function _autoRemoveOrder()
    {
        try {
            $where = [['status', '=', 0], ['create_time', '<', date('Y-m-d H:i:s', strtotime('-3 days'))]];
            [$count, $total] = [0, ($items = ShopOrder::mk()->where($where)->select())->count()];
            $items->map(function (ShopOrder $order) use ($total, &$count) {
                if ($order->payment()->findOrEmpty()->isExists()) {
                    $this->queue->message($total, ++$count, "订单 {$order->getAttr('order_no')} 存在支付记录");
                } else {
                    $this->queue->message($total, ++$count, "开始清理已取消的订单 {$order->getAttr('order_no')}");
                    ShopOrder::mk()->where(['order_no' => $order->getAttr('order_no')])->delete();
                    ShopOrderItem::mk()->where(['order_no' => $order->getAttr('order_no')])->delete();
                    $this->queue->message($total, $count, "完成清理已取消的订单 {$order->getAttr('order_no')}", 1);
                }
            });
        } catch (\Exception $exception) {
            $this->setQueueError($exception->getMessage());
        }
    }
}