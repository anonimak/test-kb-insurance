<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class CustomerController extends BaseController
{
    use ResponseTrait;

    protected $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }

    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = 10;

        $customers = $this->customerModel->paginate($perPage, 'default', $page);
        $pager = $this->customerModel->pager;

        return $this->respond([
            'status' => 200,
            'data' => $customers,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $pager->getPageCount(),
                'totalRecords' => $pager->getTotal(),
                'perPage' => $perPage
            ]
        ]);
    }

    public function show($id = null)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return $this->failNotFound('Customer not found');
        }

        return $this->respond([
            'status' => 200,
            'data' => $customer
        ]);
    }

    public function create()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'start_date_coverage' => 'required|valid_date',
            'end_date_coverage' => 'required|valid_date',
            'coverage' => 'required',
            'price' => 'required|numeric',
            'type' => 'required|integer|in_list[1,2]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getVar('name'),
            'start_date_coverage' => $this->request->getVar('start_date_coverage'),
            'end_date_coverage' => $this->request->getVar('end_date_coverage'),
            'coverage' => $this->request->getVar('coverage'),
            'price' => $this->request->getVar('price'),
            'type' => $this->request->getVar('type'),
            'is_risk_banjir' => $this->request->getVar('is_risk_banjir') ? 1 : 0,
            'is_risk_gempa' => $this->request->getVar('is_risk_gempa') ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $customerId = $this->customerModel->insert($data);

        if (!$customerId) {
            return $this->failServerError('Failed to create customer');
        }

        $customer = $this->customerModel->find($customerId);

        return $this->respondCreated([
            'status' => 201,
            'message' => 'Customer coverage created successfully',
            'data' => $customer
        ]);
    }

    public function update($id = null)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return $this->failNotFound('Customer not found');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'start_date_coverage' => 'required|valid_date',
            'end_date_coverage' => 'required|valid_date',
            'coverage' => 'required',
            'price' => 'required|numeric',
            'type' => 'required|integer|in_list[1,2]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getVar('name'),
            'start_date_coverage' => $this->request->getVar('start_date_coverage'),
            'end_date_coverage' => $this->request->getVar('end_date_coverage'),
            'coverage' => $this->request->getVar('coverage'),
            'price' => $this->request->getVar('price'),
            'type' => $this->request->getVar('type'),
            'is_risk_banjir' => $this->request->getVar('is_risk_banjir') ? 1 : 0,
            'is_risk_gempa' => $this->request->getVar('is_risk_gempa') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updated = $this->customerModel->update($id, $data);

        if (!$updated) {
            return $this->failServerError('Failed to update customer');
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Customer updated successfully',
            'data' => $this->customerModel->find($id)
        ]);
    }

    public function delete($id = null)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return $this->failNotFound('Customer not found');
        }

        if (!$this->customerModel->delete($id)) {
            return $this->failServerError('Failed to delete customer');
        }

        return $this->respondDeleted([
            'status' => 200,
            'message' => 'Customer deleted successfully'
        ]);
    }

    public function getCoverageTypes()
    {
        $types = $this->customerModel->getCoverages();

        return $this->respond([
            'status' => 200,
            'data' => $types
        ]);
    }
}
