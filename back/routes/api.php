<?php

use Illuminate\Http\Request;

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/tests', function () {
        return response()->json([
            'Creator' => [
                'Programmer' => 'Niel',
                'Second Programmer' => 'Peter'
            ]
        ]);
    });

    Route::resource('items', 'ItemsController');
    Route::resource('category', 'CategoryController');
    Route::resource('type', 'TypeController');
    Route::resource('warehouse', 'WarehouseController');
    Route::resource('client', 'ClientController');
    Route::resource('sales_order', 'SalesOrderController');
    Route::resource('delivery_receipt', 'DeliveryReceiptController');


    Route::resource('users', 'UserController');
    Route::resource('supplier', 'SupplierController');
    Route::resource('locale', 'LocaleController');
    Route::resource('purchase_order', 'PurchaseOrderController');
    Route::resource('stocks', 'StockController');
    Route::resource('logs', 'LogController');
    Route::resource('SalesReturns', 'SalesReturnController');
    Route::resource('company_assets', 'CompanyAssetsController');
    Route::resource('company_assets_type', 'CompanyAssetsTypeController');
    Route::resource('mode_of_payment', 'ModeOfPaymentController');
    Route::resource('term', 'TermController');
    Route::resource('direct_receives', 'DirectReceiveController');




    Route::get('items/serial/{serial}', 'ItemsController@serial');
    Route::post('items/showItems', 'ItemsController@showItems');
    Route::post('items/showItemGroup', 'ItemsController@showItemGroup');
    Route::post('items/showGroupData/{id}', 'ItemsController@showGroupData');
    Route::post('items/searchItem', 'ItemsController@searchItem');
    Route::post('items/searchGroup', 'ItemsController@searchGroup');


    Route::post('items/addGroup', 'ItemsController@addGroup');
    Route::get('stock/getSerialsPerItem/{id}', 'StockController@getSerialsPerItem');
    Route::get('items/barcode/{barcode}', 'ItemsController@barcode');
    Route::get('items/serial/check/{serial}', 'ItemsController@checkSerial');
    Route::get('items/serial/checkSerial/{serial}', 'ItemsController@checkSerialOnly');
    Route::put('items/qty/update/{item}', 'ItemsController@updateQty');
    Route::put('items/create/serials/{item}', 'ItemsController@createSerials');
    Route::put('items/serial/update/{serial}', 'ItemsController@updateSerial');
    Route::post('stocks/direct_receive', 'StockController@directStore');
    Route::get('logs/to_print/{serial}', 'LogController@toPrint');
    Route::post('logs/sales_return', 'LogController@salesReturn');
    Route::post('company_assets/update', 'CompanyAssetsController@updateAsset');
    Route::post('items/updateImage', 'ItemsController@updateImage');

    //--------------------Purchase Order Aprroval------------------------------//
    Route::post('purchase_order/submit_approval/{purchase_order}', 'PurchaseOrderController@submitApproval');
    Route::post('purchase_order/submit_supplier/{purchase_order}', 'PurchaseOrderController@submitSupplier');
    Route::post('purchase_order/accept/{purchase_order}', 'PurchaseOrderController@approve');
    Route::post('purchase_order/decline/{purchase_order}', 'PurchaseOrderController@decline');

    //--------------------Sales Order Aprroval------------------------------//
    Route::post('sales_order/submit_approval/{sales_order}', 'SalesOrderController@submitApproval');
    Route::post('sales_order/accept/{sales_order}', 'SalesOrderController@approve');
    Route::post('sales_order/decline/{sales_order}', 'SalesOrderController@decline');
    Route::get('sales_order/receipts/{sales_order}', 'SalesOrderController@showforReceipt');
    Route::post('sales_order/searchItem', 'SalesOrderController@searchItem');

    //--------------------Sales Return Aprroval------------------------------//

    Route::post('sales_return/accept/{sales_return}', 'SalesReturnController@approve');
    Route::post('sales_return/updateStatus', 'SalesReturnController@updateStatus');


    //--------------------Delivery Receipt------------------------------//
    Route::post('delivery_receipt/create/{sales_order}', 'DeliveryReceiptController@store');
    Route::post('delivery_receipt/confirm/{sales_order}', 'DeliveryReceiptController@confirm');
    Route::post('delivery_receipt/change_status/{id}', 'DeliveryReceiptController@changeStatus');
    Route::post('misc_summary', 'DeliveryReceiptController@misc_summary');

    //--------------------Count Function------------------------------//
    Route::post('sales_order/pending/number', 'SalesOrderController@countPending');
    Route::post('sales_order/verified/number', 'SalesOrderController@countVerified');
    Route::post('sales_order/declined/number', 'SalesOrderController@countDeclined');
    Route::post('client/number', 'ClientController@countClient');
    Route::post('updateClients', 'ClientController@updateClients');

    Route::post('dashboard/successful_order', 'DashboardController@showSuccessfulOrder');
    Route::post('dashboard/showItemInventoryReport', 'DashboardController@showItemInventoryReport');
    Route::post('dashboard/showClientInventoryReport', 'DashboardController@showClientInventoryReport');
    Route::post('dashboard/showAllInventoryReport', 'DashboardController@showAllInventoryReport');




    //--------------------Notification------------------------------//
    Route::post('notification', 'NotificationController@notification');
    Route::post('notification/alert', 'NotificationController@alert');
    Route::put('sales_order/verify/{sales_order}', 'SalesOrderController@verify');
    Route::put('sales_order/cancel/{sales_order}', 'SalesOrderController@cancelOrder');

    //--------------------Search Function------------------------------//
    Route::post('sales_order/search', 'SalesOrderController@showSearch');
    Route::post('users/search', 'UserController@showSearch');
    Route::post('warehouse/search', 'WarehouseController@showSearch');
    Route::post('client/search', 'ClientController@showSearch');
    Route::post('items/search', 'ItemsController@showSearch');
    Route::post('sales_order/client/{client}', 'SalesOrderController@showClientOrdered');
    Route::post('supplier/search', 'SupplierController@showSearch');
    Route::post('notification/forecast/{forecast}', 'NotificationController@forecast');
    Route::post('company_assets/search', 'CompanyAssetsController@searchAsset');
    Route::post('sales_return/search', 'SalesReturnController@searchSalesReturn');
});
