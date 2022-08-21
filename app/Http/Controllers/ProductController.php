<?php

namespace App\Http\Controllers;
use DB;
use Validator;
use App\Product;
use App\Sale;
use App\Inventories as Inventories;
use App\ProductCategory;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Arr;
use Carbon\Carbon;
class ProductController extends Controller
{
    const ITEM_PER_PAGE = 50;

    public function index(Request $request)
    {
        $manufacture_id = $request->manufact_id;
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = $request->get('keyword');
        $size = '';
        if(strpos($keyword, '.') !== false) {
            $exp_string = explode('.', $keyword);
            $keyword = $exp_string[0];
            $size = $exp_string[1];
        }
        $search = $request->get('search');
        $products = Product::select('*')
                            ->with(array('category','uom', 'manufacturer'))
                            ->withCount(['inventory as purchase' => function($query){
                                $query->select(DB::raw("IFNULL(SUM(quantity),0)"))->whereIn('inventory_type',['manual', 'sale_return', 'purchase']);
                             }])
                             ->withCount(['inventory as sale' => function($query){
                                $query->select(DB::raw("IFNULL(SUM(quantity),0)"))->whereIn('inventory_type',['sale','purchase_return']);
                             }])
                            ->when($keyword, function ($querycontainer) use ($keyword) {
                                  $querycontainer->where(function($query) use ($keyword) {
                                      $query->where('name', 'like', '%' . $keyword . '%')->orWhere('code', 'like', '%' . $keyword . '%');

                                  });
                            })
                            ->when($manufacture_id, function ($query) use ($manufacture_id) {
                                return $query->where('manufacturer_id', '=', $manufacture_id);     
                             })
                            ->when($size, function ($query) use ($size) {
                                return $query->where('size', '=', $size);     
                             })
                             ->when($search, function ($query){
                                return $query->where('status', '=', 'enable');     
                             })
                             ->where('type', '!=', 'variable')
                            ->paginate( $limit);
        return response()->json(new JsonResponse(['products' => $products]));
    }

    public function featured(Request $request) {
        $select =  ['id','name','sale_price','code'];
        $products = Product::select($select)->where('featured', 'yup')->get();
        return response()->json(new JsonResponse(['products' => $products]));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
       //dd($request->all());
        DB::beginTransaction();
        try {
            $type = $request->get('type');
            if($type == 'variable') {
                $validatedData = $request->validate([
                    'name' => 'required',
                ]);
                $product = new Product();
                $product->name =  $request->get('code');
                $product->code =  $request->get('code');
                $product->type = 'variable';
                $product->category_id =  $request->get('category_id');
                $product->manufacture_id =  $request->get('manufacture_id');
                $product->save();

                $variants = $request->get('variants');
                foreach($variants as $variant) {
                    $sproduct = new Product();
                    $code = ($variant['code']) ?? '';
                    $sproduct->name =  $request->get('code').$code;
                    $sproduct->code =  $request->get('code').$code;
                    $sproduct->purchase_price =  $variant['purchase_price'];
                    $sproduct->sale_price =  $variant['sale_price'];
                    $sproduct->wholesale_price =  $variant['wholesale_price'];
                    $sproduct->category_id = $product->category_id;
                    $sproduct->color = ($variant['selected_color']) ?? '';
                    $sproduct->size = ($variant['selected_size']) ?? '';
                    $sproduct->manufacture_id =  $request->get('manufacture_id');
                    $sproduct->variable_product_id = $product->id;
                    $sproduct->save();
                    if($sproduct) {
                        $inventory = new Inventories();
                        $inventory->outlet_id = '1';
                        $inventory->product_id = $sproduct->id;
                        $inventory->quantity = $variant['quantity'];
                        $inventory->inventory_type = 'purchase';
                        $inventory->save();
                    } 
                }

            } else {
                $validatedData = $request->validate([
                    //'name' => 'required|unique:products',
                    'name' => 'required',
                    'code' => 'required',
                    'purchase_price' => 'required',
                    'category_id' => 'required',
                    'sale_price' => 'required',
                ]);
                
                $product = new Product();
                $product->name =  $request->get('name');
                $product->code =  $request->get('code');
                $product->purchase_price =  $request->get('purchase_price');
                $product->sale_price =  $request->get('sale_price');
                $product->wholesale_price =  $request->get('wholesale_price');
                $product->category_id =  $request->get('category_id');
                $product->reorder_level =  $request->get('reorder');
                $product->uom_id =  $request->get('unit');
                $product->save();
                if($product) {
                    $inventory = new Inventories();
                    $inventory->outlet_id = '1';
                    $inventory->product_id = $product->id;
                    $inventory->quantity = $request->get('quantity');
                    $inventory->inventory_type = 'manual';
                    $inventory->save();
                } 
            }
            
            DB::commit();
            return $product;
        }
        catch(Exception $e) {
            DB::rollback();
            return response()->json(new JsonResponse($e->getMessage()));
        }
        
    }

    public function show(Product $product)
    {
        return $product->load(['category','uom']);
    }

    public function edit(Product $product)
    {
        //
    }

    public function update(Request $request, Product $product)
    {
       // dd($request->all());
        $measure = ($request->get('uom')) ?? null ;
        $measure = ($measure) ? $measure['id']: null;
        $validatedData = $request->validate([
            //'name' => 'required|unique:products,name,'.$product->id,
            'name' => 'required',
            'purchase_price' => 'required',
            'category_id' => 'required',
            'sale_price' => 'required',
            'featured' => 'required',
            'code' => 'required',
        ]);
        
        $product->name =  $request->get('name');
        $product->purchase_price =  $request->get('purchase_price');
        $product->sale_price =  $request->get('sale_price');
        $product->wholesale_price =  $request->get('wholesale_price');
        $product->category_id =  $request->get('category_id');
        $product->status =  $request->get('status');
        $product->featured =  $request->get('featured');
         $product->code =  $request->get('code');
         $product->size =  $request->get('size') ?? 0;
        $product->uom_id =  $measure;
        $product->save();
        return $product;
    }

    public function destroy(Product $product)
    {
        //
    }

    public function edit_price(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_price' => 'required|integer|min:1',
        ]);
 
        if ($validator->fails()) {
            return response()->json(new JsonResponse(['errors' =>  $validator->errors()]), 500);
        }

        $new_price = $request->new_price;
        $products = $request->multipleSelection;
        foreach($products as $product) {
            $pro = Product::find($product);
            $pro->sale_price = $new_price;
            $pro->save();
        }
    }

    public function get_product_stock($id, $json=true) {
        $productStock = DB::select("SELECT IFNULL(sum(IFNULL(totaljama,0)-IFNULL(totalnaam,0)),0) as acc_total from 
        (SELECT sum(quantity) as totaljama from inventories where product_id = $id AND inventory_type IN('manual', 'sale_return', 'purchase')) as jama 
        JOIN 
        (SELECT sum(quantity) as totalnaam from inventories where product_id = $id AND inventory_type IN('sale','purchase_return')) as naam");
        foreach($productStock as $total){
            $total_stock =  $total->acc_total;
        }
        if($json)
            return response()->json(new JsonResponse(['stock' =>  $total_stock]));
        else
            return $total_stock;
    }

    public function getstock(Request $request, $id = '') {
       // print_r($request->id);die();
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $product = Product::find($request->id)->inventory()->orderBy('created_at', 'desc')->paginate($limit);
        $totalstock = $this->get_product_stock($request->id, false);
        return response()->json(new JsonResponse(['stock' =>  $product, 'total_stock' => $totalstock]));
    }

    public function getTotalStock($produtid) {
        $balance =  DB::select("SELECT IFNULL(sum(IFNULL(totaljama,0)-IFNULL(totalnaam,0)),0) as acc_total from 
        (SELECT sum(amount) as totaljama from account_transactions where jama_account = $produtid) as jama 
        JOIN 
        (SELECT sum(amount) as totalnaam from account_transactions where naam_account = $produtid) as naam");
    }

    public function addstock(Request $request) {
        $stock = new Inventories();
        $stock->quantity = $request->get('quantity');
        $stock->outlet_id = $request->get('outlet_id');
        $stock->product_id = $request->get('product_id');
        $stock->remarks = $request->get('remarks');
        $stock->inventory_type = 'manual';
        $stock->save();
        return response()->json(new JsonResponse(['stock' =>  $stock]));

    }

    public function import_products(Request $request) {
        DB::beginTransaction();
        try {
            $csvdata = $request->get('results');
            foreach($csvdata as $record) {
                $product = new Product();
                $product->name =  $record['name'];
                $product->code =  $record['code'];
                $product->purchase_price =  $record['purchase_price'];
                $product->sale_price =  $record['sale_price'];
                $product->wholesale_price =  $record['wholesale_price'];
                $product->category_id =  $record['category_id'];
                $product->reorder_level =  $record['reorder_level'];
                $product->uom_id =  $record['uom_id'];
                $product->save();
                if($product) {
                    $inventory = new Inventories();
                    $inventory->outlet_id = '1';
                    $inventory->product_id = $product->id;
                    $inventory->quantity = $record['quantity'];
                    $inventory->inventory_type = 'manual';
                    $inventory->save();
                } 
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollback();
            return response()->json(new JsonResponse($e->getMessage()));
        }
    }
    public function stock_data_dashboard(){
        $stocks = DB::select("SELECT sum(quantity) as `totaljama` from inventories WHERE inventory_type IN('manual', 'sale_return', 'purchase')");
        return response()->json(new JsonResponse(['stock' =>  $stocks]));
    }

    public function get_stock_print(){
        $date = date("Y/m/d");
        $date = Carbon::parse($date)->startOfDay();  
        $sales = DB::SELECT("SELECT p.code,p.name,sum(sp.quantity) as qty,Round(sum(sp.price*sp.quantity - (sp.price*sp.quantity * sp.discount1/100)),2) as sale FROM `products` p RIGHT JOIN sale_products sp ON p.id = sp.product_id WHERE DATE(sp.created_at) = '$date' GROUP BY p.id, DATE(sp.created_at)");       
        return response()->json(new JsonResponse(['sales' => $sales]));
    }

    public function stock_value_report() {
        $products = DB::select("SELECT p.name,p.code, p.purchase_price, p.size, 
        (SELECT IFNULL(sum(quantity),0) from inventories i 
            where inventory_type in('sale','purchase_return') 
            AND i.product_id = p.id ) as sale, 
            (SELECT IFNULL(sum(quantity),0) 
                from inventories i 
                where inventory_type in('purchase','sale_return', 'manual') 
                AND i.product_id = p.id ) as purchase FROM products p");
        

        return response()->json(new JsonResponse(['products' => $products]));
    }

    public function stock_retial_value_report() {
        $products = DB::select("SELECT p.name,p.code, p.purchase_price, p.sale_price, p.size, 
        (SELECT IFNULL(sum(quantity),0) from inventories i 
            where inventory_type in('sale','purchase_return') 
            AND i.product_id = p.id ) as sale, 
            (SELECT IFNULL(sum(quantity),0) 
                from inventories i 
                where inventory_type in('purchase','sale_return', 'manual') 
                AND i.product_id = p.id ) as purchase FROM products p");
        

        return response()->json(new JsonResponse(['products' => $products]));
    }



}
