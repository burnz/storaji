<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Product;
use App\Order;
use App\OrderDetail;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(){
        $this->content = array();
    }

    public function get(Request $request){
        $order = Order::with(['order_detail.product', 'customer'])->get();

        if($this->content['data'] = $order){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function find(Request $request){
        $order = Order::with(['order_detail.product', 'customer'])->find($request->id);

        if($this->content['data'] = $order){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function add(Request $request)
    {
        $product = Product::find($request->input('order_detail.product.id'));
        if ($product->stock >= $request->input('order_detail.amount')){
          $product->update([
            'stock' => (int) $product->stock - $request->input('order_detail.amount')
          ]);
          
          $order = Order::create([
            'customer_id' => $request->input('customer.id')
          ]);
  
          $order_detail = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'amount' => $request->input('order_detail.amount')
          ]);

          if($this->content['data'] = Order::with(['order_detail.product', 'customer'])->get()){
            $this->content['status'] = 200;
            return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
          }
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }

    public function update(Request $request)
    {
        $order = Order::with(['order_detail.product', 'customer'])->find($request->id);
        
        $order->update([
          'customer_id' => $request->input('customer_id')
        ]);

        $order_detail = OrderDetail::where('order_id', $order->id);

        $order_detail->update([
          'product_id' => $request->input('product_id'),
          'amount' => $request->input('amount')
        ]);

        if($this->content['data'] = $order){
          $this->content['status'] = 200;
          return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
        }

        $this->content['error'] = "Server Error";
        $this->content['status'] = 500;

        return response()->json($this->content, $this->content['status'], [], JSON_NUMERIC_CHECK);
    }
}
