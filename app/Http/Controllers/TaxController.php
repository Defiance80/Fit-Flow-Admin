<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Validator;
use App\Services\BootstrapTableService;
use Throwable;

class TaxController extends Controller
{
    public function index()
    {
        ResponseService::noAnyPermissionThenRedirect(['taxes-list', 'taxes-create', 'taxes-edit', 'taxes-delete']);
        return view('taxes.index', ['type_menu' => 'taxes']);
    }

    public function store(Request $request)
    {
        ResponseService::noPermissionThenSendJson('taxes-create');
        $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:999.99',
        ]);
        try {
            $data = $request->all();
            $tax = Tax::create($data);

            ResponseService::successResponse("Tax Created Successfully");
        } catch (Throwable $th) {
            ResponseService::logErrorRedirect($th);
            ResponseService::errorRedirectResponse('Failed to create Tax');
        }
    }

    public function show(Request $request, $id)
    {
        ResponseService::noPermissionThenSendJson('taxes-list');

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $search = $request->input('search');
        $showDeleted = $request->input('show_deleted');

        $sql = Tax::query()
            ->when($showDeleted == 1 || $showDeleted === '1', function ($query) {
                $query->onlyTrashed();
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('percentage', 'LIKE', "%$search%");
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
                $operate = BootstrapTableService::restoreButton(route('taxes.restore', $row->id));
                $operate .= BootstrapTableService::trashButton(route('taxes.trash', $row->id));
            } else {
                $operate = BootstrapTableService::editButton(route('taxes.update', $row->id), true, '#taxEditModal', $row->id);
                $operate .= BootstrapTableService::deleteButton(route('taxes.destroy', $row->id));
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

    public function update(Request $request, Tax $tax)
    {
        ResponseService::noPermissionThenRedirect('taxes-edit');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:1|max:99.99',
        ]);

        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }

        try {
            $data = $validator->validated();
            // is_active is not updated from edit form - keep existing value

            $tax->update($data);

            return ResponseService::successResponse('Tax Updated Successfully');
        } catch (Exception $th) {
            ResponseService::logErrorRedirect($th, "TaxController -> update()");
            return ResponseService::errorResponse();
        }
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();
        return ResponseService::successResponse('Tax Deleted Successfully');
    }

    /**
     * Restore a soft-deleted tax
     */
    public function restore($id)
    {
        try {
            $tax = Tax::onlyTrashed()->findOrFail($id);
            $tax->restore();
            return ResponseService::successResponse('Tax Restored Successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorRedirect($th, 'TaxController -> restore');
            return ResponseService::errorResponse('Failed to restore tax.');
        }
    }

    /**
     * Permanently delete a soft-deleted tax
     */
    public function trash($id)
    {
        try {
            $tax = Tax::onlyTrashed()->findOrFail($id);
            $tax->forceDelete();
            return ResponseService::successResponse('Tax Permanently Deleted Successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorRedirect($th, 'TaxController -> trash');
            return ResponseService::errorResponse('Failed to permanently delete tax.');
        }
    }
} 