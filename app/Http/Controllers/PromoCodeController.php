<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Validator;
use App\Services\BootstrapTableService;
use Exception;
use Illuminate\Support\Facades\Auth;

class PromoCodeController extends Controller
{
    public function index()
    {
       // ResponseService::noAnyPermissionThenRedirect(['promo-code-list', 'promo-code-create', 'promo-code-edit', 'promo-code-delete']);
        return view('promo-codes.index', ['type_menu' => 'promo-codes']);
    }

    public function store(Request $request)
    {
        //ResponseService::noPermissionThenSendJson('promo-code-create');

        // Custom validation for max_discount_amount based on discount_type
        $rules = [
            'promo_code' => 'required|string|max:255|unique:promo_codes,promo_code',
            'message' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'no_of_users' => 'nullable|numeric|min:0',
            'minimum_order_amount' => 'required|numeric|min:0|max:999999999.99',
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|string|max:255',
        ];

        // Make max_discount_amount required only for percentage discount type
        if ($request->discount_type === 'percentage') {
            $rules['max_discount_amount'] = 'required|numeric|min:0.01|max:999999999.99';
            // For percentage discount type, discount cannot exceed 100%
            $rules['discount'] = 'required|numeric|min:0|max:100';
        } else {
            $rules['max_discount_amount'] = 'nullable|numeric|min:0|max:999999999.99';
            // For fixed amount, set reasonable max limit
            $rules['discount'] = 'required|numeric|min:0|max:999999999.99';
        }

        $validator = Validator::make($request->all(), $rules);

        // Custom validation: For fixed amount discount
        if ($request->discount_type === 'amount') {
            $validator->after(function ($validator) use ($request) {
                // Discount should be less than minimum order amount (not equal)
                if ($request->discount >= $request->minimum_order_amount) {
                    $validator->errors()->add('discount', 'Discount amount must be less than Minimum Order Amount.');
                }
                
                // max_discount_amount should be >= discount if set
                if ($request->has('max_discount_amount') && $request->max_discount_amount !== null && $request->max_discount_amount != 0) {
                    if ($request->max_discount_amount < $request->discount) {
                        $validator->errors()->add('max_discount_amount', 'Max Discount Amount must be greater than or equal to Discount amount.');
                    }
                }
            });
        }
        
        // Custom validation: For percentage discount type
        if ($request->discount_type === 'percentage') {
            $validator->after(function ($validator) use ($request) {
                // Minimum order amount should be greater than max discount amount (not equal)
                // For percentage type, max_discount_amount is required, so it should always be present
                $maxDiscountAmount = $request->input('max_discount_amount', 0);
                $minimumOrderAmount = $request->input('minimum_order_amount', 0);
                
                // Convert to float for proper comparison
                $maxDiscountAmount = floatval($maxDiscountAmount);
                $minimumOrderAmount = floatval($minimumOrderAmount);
                
                if ($maxDiscountAmount > 0 && $minimumOrderAmount > 0) {
                    if ($minimumOrderAmount <= $maxDiscountAmount) {
                        $validator->errors()->add('max_discount_amount', 'Max Discount Amount must be less than Minimum Order Amount.');
                        $validator->errors()->add('minimum_order_amount', 'Minimum Order Amount must be greater than Max Discount Amount.');
                    }
                }
            });
        }

        if ($validator->fails()) {
            // Check if request is AJAX (jQuery sets X-Requested-With header)
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                $errors = $validator->errors()->all();
                $firstError = $validator->errors()->first();
                // Get specific error for promo_code if it exists
                if ($validator->errors()->has('promo_code')) {
                    $firstError = $validator->errors()->first('promo_code');
                }
                return ResponseService::validationError($firstError, ['errors' => $errors]);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $data = $request->all();
            $data['user_id'] = Auth::id();

            // Handle max_discount_amount based on discount type
            if ($request->discount_type === 'amount') {
                // For fixed amount discount, if not provided or empty, set to 0 (no additional cap)
                // The discount amount itself is the cap for fixed amount discounts
                if (!isset($data['max_discount_amount']) || $data['max_discount_amount'] === '' || $data['max_discount_amount'] == 0) {
                    $data['max_discount_amount'] = 0;
                }
            }
            // For percentage discount, max_discount_amount is already validated and required (min:0.01)

            // Set default values for repeat_usage fields
            $data['repeat_usage'] = false;
            $data['no_of_repeat_usage'] = 0;

            $promoCode = PromoCode::create($data);

            ResponseService::successResponse("Promo Code Created Successfully");
        } catch (Exception $th) {
            ResponseService::logErrorRedirect($th);
            ResponseService::errorRedirectResponse('Failed to create Promo Code');
        }
    }

    public function show(Request $request, $id)
    {
        //ResponseService::noPermissionThenSendJson('taxes-list');

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $search = $request->input('search');
        $showDeleted = $request->input('show_deleted');

        $sql = PromoCode::query()
            ->when($showDeleted == 1 || $showDeleted === '1', function ($query) {
                $query->onlyTrashed();
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('promo_code', 'LIKE', "%$search%")
                    ->orWhere('message', 'LIKE', "%$search%");
                });
            });

        $sql->orderBy($sort, $order);

        $total = $sql->count();
        $result = $sql->skip($offset)->take($limit)->get();

        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];
        $no = $offset + 1;

        foreach ($result as $row) {
            if ($showDeleted == 1 || $showDeleted === '1') {
                $operate = BootstrapTableService::restoreButton(route('promo-codes.restore', $row->id));
                $operate .= BootstrapTableService::trashButton(route('promo-codes.trash', $row->id));
            } else {
                $operate = BootstrapTableService::editButton(route('promo-codes.update', $row->id), true, '#promoCodeEditModal', $row->id);
                $operate .= BootstrapTableService::deleteButton(route('promo-codes.destroy', $row->id));
            }

            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    // public function edit(Tax $tax)
    // {
    //     return view('taxes.edit', compact('tax'));
    // }

    public function update(Request $request, PromoCode $promoCode)
    {
        //ResponseService::noPermissionThenRedirect('tax-edit');

        // Custom validation for max_discount_amount based on discount_type
        $rules = [
            'promo_code' => 'required|string|max:255|unique:promo_codes,promo_code,' . $promoCode->id,
            'message' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'no_of_users' => 'nullable|numeric|min:0',
            'minimum_order_amount' => 'required|numeric|min:0|max:999999999.99',
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|string|max:255',
        ];

        // max_discount_amount validation based on discount type
        if ($request->discount_type === 'percentage') {
            // For percentage discount type, max_discount_amount is required and must be > 0
            $rules['max_discount_amount'] = 'required|numeric|min:0.01|max:999999999.99';
            // For percentage discount type, discount cannot exceed 100%
            $rules['discount'] = 'required|numeric|min:0|max:100';
        } else {
            // For fixed amount discount type, max_discount_amount is optional
            $rules['max_discount_amount'] = 'nullable|numeric|min:0|max:999999999.99';
            // For fixed amount, set reasonable max limit
            $rules['discount'] = 'required|numeric|min:0|max:999999999.99';
        }

        $validator = Validator::make($request->all(), $rules);

        // Custom validation: For fixed amount discount
        if ($request->discount_type === 'amount') {
            $validator->after(function ($validator) use ($request) {
                // Discount should be less than minimum order amount (not equal)
                if ($request->discount >= $request->minimum_order_amount) {
                    $validator->errors()->add('discount', 'Discount amount must be less than Minimum Order Amount.');
                }
                
                // max_discount_amount should be >= discount if set
                if ($request->has('max_discount_amount') && $request->max_discount_amount !== null && $request->max_discount_amount != 0) {
                    if ($request->max_discount_amount < $request->discount) {
                        $validator->errors()->add('max_discount_amount', 'Max Discount Amount must be greater than or equal to Discount amount.');
                    }
                }
            });
        }
        
        // Custom validation: For percentage discount type
        if ($request->discount_type === 'percentage') {
            $validator->after(function ($validator) use ($request) {
                // Minimum order amount should be greater than max discount amount (not equal)
                // For percentage type, max_discount_amount is required, so it should always be present
                $maxDiscountAmount = $request->input('max_discount_amount', 0);
                $minimumOrderAmount = $request->input('minimum_order_amount', 0);
                
                // Convert to float for proper comparison
                $maxDiscountAmount = floatval($maxDiscountAmount);
                $minimumOrderAmount = floatval($minimumOrderAmount);
                
                if ($maxDiscountAmount > 0 && $minimumOrderAmount > 0) {
                    if ($minimumOrderAmount <= $maxDiscountAmount) {
                        $validator->errors()->add('max_discount_amount', 'Max Discount Amount must be less than Minimum Order Amount.');
                        $validator->errors()->add('minimum_order_amount', 'Minimum Order Amount must be greater than Max Discount Amount.');
                    }
                }
            });
        }

        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }

        try {
            $data = $validator->validated();

            // Handle max_discount_amount based on discount type
            if ($request->discount_type === 'amount') {
                // For fixed amount discount, if not provided or empty, set to 0 (no additional cap)
                // The discount amount itself is the cap for fixed amount discounts
                if (!isset($data['max_discount_amount']) || $data['max_discount_amount'] === '' || $data['max_discount_amount'] == 0) {
                    $data['max_discount_amount'] = 0;
                }
            }
            // For percentage discount, max_discount_amount is already validated and required (min:0.01)

            // Set default values for repeat_usage fields
            $data['repeat_usage'] = false;
            $data['no_of_repeat_usage'] = 0;

            $promoCode->update($data);

            return ResponseService::successResponse('Promo Code Updated Successfully');
        } catch (Exception $th) {
            ResponseService::logErrorRedirect($th, "PromoCodeController -> update()");
            return ResponseService::errorResponse();
        }
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();
        return ResponseService::successResponse('Promo Code Deleted Successfully');
    }

    /**
     * Restore a soft-deleted promo code
     */
    public function restore($id)
    {
        try {
            $promoCode = PromoCode::onlyTrashed()->findOrFail($id);
            $promoCode->restore();
            return ResponseService::successResponse('Promo Code Restored Successfully');
        } catch (Exception $th) {
            ResponseService::logErrorRedirect($th, 'PromoCodeController -> restore');
            return ResponseService::errorResponse('Failed to restore promo code.');
        }
    }

    /**
     * Permanently delete a soft-deleted promo code
     */
    public function trash($id)
    {
        try {
            $promoCode = PromoCode::onlyTrashed()->findOrFail($id);
            $promoCode->forceDelete();
            return ResponseService::successResponse('Promo Code Permanently Deleted Successfully');
        } catch (Exception $th) {
            ResponseService::logErrorRedirect($th, 'PromoCodeController -> trash');
            return ResponseService::errorResponse('Failed to permanently delete promo code.');
        }
    }
} 