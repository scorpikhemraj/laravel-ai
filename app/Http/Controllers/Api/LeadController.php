<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Column-level search filters
        if ($filterId = $request->input('filter_id')) {
            $query->where('id', $filterId);
        }
        if ($filterName = $request->input('filter_name')) {
            $query->where(function ($q) use ($filterName) {
                $q->where('first_name', 'like', "%{$filterName}%")
                    ->orWhere('last_name', 'like', "%{$filterName}%");
            });
        }
        if ($filterEmail = $request->input('filter_email')) {
            $query->where('email', 'like', "%{$filterEmail}%");
        }
        if ($filterPhone = $request->input('filter_phone')) {
            $query->where('phone', 'like', "%{$filterPhone}%");
        }
        if ($filterCompany = $request->input('filter_company')) {
            $query->where('company', 'like', "%{$filterCompany}%");
        }
        if ($filterStatus = $request->input('filter_status')) {
            $query->where('status', $filterStatus);
        }
        if ($filterSource = $request->input('filter_source')) {
            $query->where('source', $filterSource);
        }
        if ($filterValue = $request->input('filter_value')) {
            $query->where('value', 'like', "%{$filterValue}%");
        }
        if ($request->has('filter_is_favorite') && $request->input('filter_is_favorite') !== '') {
            $val = filter_var($request->input('filter_is_favorite'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_favorite', $val);
        }
        if ($filterAddress = $request->input('filter_address')) {
            $query->where('address', 'like', "%{$filterAddress}%");
        }
        if ($filterState = $request->input('filter_state')) {
            $query->where('state', 'like', "%{$filterState}%");
        }
        if ($filterPostalCode = $request->input('filter_postal_code')) {
            $query->where('postal_code', 'like', "%{$filterPostalCode}%");
        }
        if ($filterIndustry = $request->input('filter_industry')) {
            $query->where('industry', 'like', "%{$filterIndustry}%");
        }
        if ($filterAnnualRevenue = $request->input('filter_annual_revenue')) {
            $query->where('annual_revenue', 'like', "%{$filterAnnualRevenue}%");
        }
        if ($filterEmployees = $request->input('filter_number_of_employees')) {
            $query->where('number_of_employees', 'like', "%{$filterEmployees}%");
        }
        if ($filterWebsite = $request->input('filter_website')) {
            $query->where('website', 'like', "%{$filterWebsite}%");
        }
        if ($filterLinkedin = $request->input('filter_linkedin_url')) {
            $query->where('linkedin_url', 'like', "%{$filterLinkedin}%");
        }
        if ($filterScore = $request->input('filter_lead_score')) {
            $query->where('lead_score', 'like', "%{$filterScore}%");
        }
        if ($filterNotes = $request->input('filter_notes')) {
            $query->where('notes', 'like', "%{$filterNotes}%");
        }

        $sortField = $request->input('sortField', 'created_at');
        $sortOrder = $request->input('sortOrder') == 1 ? 'asc' : 'desc';

        $allowedSorts = [
            'id', 'first_name', 'last_name', 'email', 'phone', 'company', 'status', 'is_favorite',
            'address', 'state', 'postal_code', 'industry', 'annual_revenue', 'number_of_employees',
            'website', 'linkedin_url', 'lead_score', 'notes'
        ];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->input('perPage', 10);
        $leads = $query->paginate($perPage);

        return response()->json($leads);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'status' => 'required|in:new,contacted,qualified,lost',
            'source' => 'required|in:website,referral,social_media,cold_call,advertising',
            'value' => 'nullable|numeric|min:0',
            'is_favorite' => 'nullable|boolean',
        ]);

        $lead = Lead::create($validated);

        return response()->json($lead, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead): JsonResponse
    {
        return response()->json($lead);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'status' => 'required|in:new,contacted,qualified,lost',
            'source' => 'required|in:website,referral,social_media,cold_call,advertising',
            'value' => 'nullable|numeric|min:0',
            'is_favorite' => 'nullable|boolean',
        ]);

        $lead->update($validated);

        return response()->json($lead);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully']);
    }

    /**
     * Remove multiple specified resources from storage.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        if (! empty($ids)) {
            Lead::whereIn('id', $ids)->delete();
        }

        return response()->json(['message' => 'Leads deleted successfully']);
    }

    /**
     * Update multiple specified resources in storage.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $field = $request->input('field');
        $value = $request->input('value');

        $allowedFields = ['status', 'source', 'is_favorite'];

        if (! empty($ids) && in_array($field, $allowedFields)) {
            if ($field === 'status' && ! in_array($value, ['new', 'contacted', 'qualified', 'lost'])) {
                return response()->json(['message' => 'Invalid status value'], 422);
            }
            if ($field === 'source' && ! in_array($value, ['website', 'referral', 'social_media', 'cold_call', 'advertising'])) {
                return response()->json(['message' => 'Invalid source value'], 422);
            }
            if ($field === 'is_favorite') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            Lead::whereIn('id', $ids)->update([$field => $value]);
        }

        return response()->json(['message' => 'Leads updated successfully']);
    }
}
