<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\Company\StoreCompanyRequest;
use App\Http\Requests\Admin\Company\UpdateCompanyRequest;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $companies)
    {
    }

    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $showDeleted = (bool) $request->boolean('show_deleted', false);

        $companies = $this->companies->list([
            'search' => $search,
            'show_deleted' => $showDeleted,
        ]);

        return view('admin.companies.index', [
            'filters' => [
                'search' => $search,
                'show_deleted' => $showDeleted,
            ],
            'companies' => $companies->through(fn (Company $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'deleted_at' => $c->deleted_at,
                'created_at' => $c->created_at?->toDateTimeString(),
            ]),
        ]);
    }

    public function create(): View
    {
        return view('admin.companies.create');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $this->companies->create($request->validated());
        return redirect()->route('admin.companies.index')->with('success', __('Company created.'));
    }

    public function edit(Company $company): View
    {
        return view('admin.companies.edit', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'deleted_at' => $company->deleted_at,
            ],
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $this->companies->update($company, $request->validated());
        return redirect()->route('admin.companies.index')->with('success', __('Company updated.'));
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->companies->delete($company);
        return back()->with('success', __('Company deleted.'));
    }

    public function restore(int $id): RedirectResponse
    {
        $this->companies->restore($id);
        return redirect()->route('admin.companies.index')->with('success', __('Company restored.'));
    }
}
