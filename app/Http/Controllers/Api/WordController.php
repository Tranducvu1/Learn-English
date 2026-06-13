<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Word::query();

        if ($request->filled('hsk')) {
            $query->where('hsk', (int) $request->input('hsk'));
        }
        if ($request->filled('topic')) {
            $query->where('topic_id', $request->string('topic'));
        }
        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($builder) use ($q) {
                $builder->where('hanzi', 'like', $q)
                    ->orWhere('pinyin', 'like', $q)
                    ->orWhere('vietnamese', 'like', $q)
                    ->orWhere('english', 'like', $q);
            });
        }

        $perPage = min((int) $request->input('per_page', 50), 5000);
        $paginated = $query->orderBy('id')->paginate($perPage);

        return response()->json([
            'meta' => [
                'count' => Word::count(),
                'page' => $paginated->currentPage(),
                'perPage' => $paginated->perPage(),
                'totalPages' => $paginated->lastPage(),
            ],
            'words' => $paginated->getCollection()->map(fn (Word $w) => $this->formatWord($w)),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $word = Word::where('id', $id)->orWhere('hanzi', $id)->firstOrFail();

        return response()->json(['word' => $this->formatWord($word)]);
    }

    private function formatWord(Word $w): array
    {
        return [
            'id' => $w->id,
            'hanzi' => $w->hanzi,
            'pinyin' => $w->pinyin,
            'vietnamese' => $w->vietnamese,
            'english' => $w->english,
            'hsk' => $w->hsk,
            'topic' => $w->topic_id,
            'example' => $w->example,
        ];
    }
}
