<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Models\PurchaseRequestLine;
use App\Models\PurchaseRequest;
use App\Models\Product;  // modèle produit (référentiel)
use App\Models\Unit;     // modèle unité
use App\Models\Category; // catégorie
use App\Models\BudgetLine;   // ligne budgétaire
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;

class PurchaseRequestLineController extends Controller
{
    /**
     * Liste les lignes d'une demande d'achat.
     */
    public function index(Request $request)
    {
        // Validation optionnelle : demander un purchase_request_header_id
        $request->validate([
            'purchase_request_header_id' => 'required|exists:purchase_request,id',
        ]);

        $headerId = $request->purchase_request_header_id;

        // Récupérer toutes les lignes associées à cette demande d'achat
        $lines = PurchaseRequestLine::where('purchase_request_header_id', $headerId)->get();

        return response()->json($lines);
    }

    /**
     * Ajoute une ligne à une demande d'achat.
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_request_header_id' => 'required|exists:purchase_request,id',
            'products' => 'array',
            'products.*.product_name' => 'required|string',
            'products.*.quantity' => 'required|integer',
            'products.*.unit' => 'required|string',
            'products.*.estimated_price' => 'nullable|numeric',
            'products.*.category' => 'nullable|string',
            'products.*.budget_code' => 'nullable|string',
            'pack_ids' => 'array',
            'pack_ids.*' => 'integer|exists:packs,id',
        ]);

        $headerId = $request->purchase_request_header_id;

        // Insert produits simples
        foreach ($request->input('products', []) as $product) {
            PurchaseRequestLine::create([
                'purchase_request_header_id' => $headerId,
                'product_name' => $product['product_name'],
                'quantity' => $product['quantity'],
                'unit' => $product['unit'],
                'estimated_price' => $product['estimated_price'] ?? null,
                'category' => $product['category'] ?? null,
                'budget_code' => $product['budget_code'] ?? null,
            ]);
        }

        // Récupérer les produits des packs
        $packIds = $request->input('pack_ids', []);
        $packs = Pack::with('products')->whereIn('id', $packIds)->get();

        foreach ($packs as $pack) {
            foreach ($pack->products as $packProduct) {
                PurchaseRequestLine::create([
                    'purchase_request_header_id' => $headerId,
                    'product_name' => $packProduct->product_name,
                    'quantity' => $packProduct->quantity,
                    'unit' => $packProduct->unit,
                    'estimated_price' => $packProduct->estimated_price,
                    'category' => $packProduct->category,
                    'budget_code' => $packProduct->budget_code,
                ]);
            }
        }

        return response()->json(['message' => 'Products and packs added successfully.']);
    }
    /**
     * Affiche une ligne spécifique.
     */
    public function show($id)
    {
        $line = PurchaseRequestLine::findOrFail($id);
        return response()->json($line);
    }

    /**
     * Met à jour une ligne.
     */
    public function update(Request $request, $id)
    {
        $line = PurchaseRequestLine::findOrFail($id);

        $validated = $request->validate([
            'product_code' => ['sometimes', 'string', function ($attribute, $value, $fail) {
                if (!Product::where('code', $value)->exists()) {
                    $fail('La référence produit est invalide.');
                }
            }],
            'quantity' => 'sometimes|integer|min:1',
            'unit_id' => ['sometimes', 'integer', Rule::exists('units', 'id')],
            'estimated_unit_price' => 'nullable|numeric|min:0',
            'category_id' => ['sometimes', 'integer', Rule::exists('categories', 'id')],
            'budget_rubric_id' => ['sometimes', 'integer', Rule::exists('budget_rubrics', 'id')],
            'budget_line_id' => ['sometimes', 'integer', Rule::exists('budget_lines', 'id')],
            'technical_justification' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        // Si code produit modifié, mettre à jour la désignation aussi
        if (isset($validated['product_code'])) {
            $product = Product::where('code', $validated['product_code'])->first();
            $line->product_code = $validated['product_code'];
            $line->product_designation = $product->designation;
        }

        if (isset($validated['quantity'])) {
            $line->quantity = $validated['quantity'];
        }

        if (isset($validated['unit_id'])) {
            $line->unit_id = $validated['unit_id'];
        }

        if (array_key_exists('estimated_unit_price', $validated)) {
            $line->estimated_unit_price = $validated['estimated_unit_price'];
        }

        if (isset($validated['category_id'])) {
            $line->category_id = $validated['category_id'];
        }

        if (isset($validated['budget_rubric_id'])) {
            $line->budget_rubric_id = $validated['budget_rubric_id'];
        }

        if (isset($validated['budget_line_id'])) {
            $line->budget_line_id = $validated['budget_line_id'];
        }

        if (array_key_exists('technical_justification', $validated)) {
            $line->technical_justification = $validated['technical_justification'];
        }

        if (array_key_exists('observations', $validated)) {
            $line->observations = $validated['observations'];
        }

        // Recalculer montant total estimé
        $qty = $line->quantity;
        $price = $line->estimated_unit_price ?? 0;
        $line->total_estimated_amount = $qty * $price;

        $line->save();

        return response()->json($line);
    }

    /**
     * Supprime une ou plusieurs lignes (en batch).
     * On peut passer un tableau d'IDs dans la requête (ex: ?ids[]=1&ids[]=2)
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['error' => 'Liste des IDs requise'], Response::HTTP_BAD_REQUEST);
        }

        PurchaseRequestLine::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Lignes supprimées avec succès']);
    }
}
