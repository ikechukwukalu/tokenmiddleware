<?php

namespace Ikechukwukalu\Tokenmiddleware\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Ikechukwukalu\Tokenmiddleware\Models\Novel;

class NovelController extends Controller
{

    /**
     * Create Novel.
     *
     * @bodyParam name string required Example: Once upon a time
     * @bodyParam isbn string required Example: 978-3-16-148410-0
     * @bodyParam authors string[] required Example: ['John Doe', 'Jane Doe']
     * @bodyParam country string required Example: Nigeria
     * @bodyParam number_of_pages int required Example: 1090
     * @bodyParam publisher string required Example: Walt Disney
     * @bodyParam release_date string required Example: 2022-01-01
     *
     * @response 200 {
     * "status": "success",
     * "status_code": 200,
     * "data": {
     *      "message": string
     *      "access_token": string
     *  }
     * }
     *
     * @authenticated
     * @group Auth APIs
     * @subgroup Sample Require Token APIs
     * @subgroupDescription This is a Novel Management API for
     * testing the <b>require.token</b> middleware
     */
    public function createNovel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:6|max:100',
            'isbn' => 'required|min:6|max:100|unique:novels',
            'authors' => 'required|min:6|max:1000',
            'country' => 'required|max:100',
            'number_of_pages' => 'required|digits_between:1,5',
            'publisher' => 'required|min:6|max:100',
            'release_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            $data = (array) $validator->errors()->all();
            return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'), 500, $data);
        }

        if ($novel = Novel::create((array) $validator->validated())) {
            $data = $novel;
            return $this->httpJsonResponse(trans('tokenmiddleware::general.success'), 200, $data);
        }

        $data = ['message' => 'Novel could not be created'];
        return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'), 500, $data);
    }

    /**
     * Fetch novels.
     *
     * @urlParam id string The ID of the novel Example: 1
     *
     * @response 200 {
     * "status": "success",
     * "status_code": 200,
     * "data": {
     *        [
     *          'name': string,
     *          'isbn': string,
     *          'authors': array,
     *          'number_of_pages': int,
     *          'publisher': string,
     *          'country': string,
     *          'release_date': date,
     *        ],
     *        [
     *          'name': string,
     *          'isbn': string,
     *          'authors': array,
     *          'number_of_pages': int,
     *          'publisher': string,
     *          'country': string,
     *          'release_date': date,
     *        ]
     *  }
     * }
     *
     * @authenticated
     * @group Auth APIs
     * @subgroup Sample Require Token APIs
     * @subgroupDescription This is a Novel Management API for
     * testing the <b>require.token</b> middleware
     */
    public function listNovels(Request $request, $id = null): JsonResponse
    {
        if (isset($id)) {
            $data = Novel::find($id);
            return $this->httpJsonResponse(trans('tokenmiddleware::general.success'), 200, $data);
        }

        $data = Novel::paginate(10);
        return $this->httpJsonResponse(trans('tokenmiddleware::general.success'), 200, $data);
    }

    /**
     * Update novel.
     *
     * @urlParam id string required The ID of the novel Example: 1
     * @bodyParam id string required This ID is gotten from the URL param Example: Once upon a time
     * @bodyParam name string Example: Once upon a time
     * @bodyParam isbn string Example: 978-3-16-148410-0
     * @bodyParam authors string[] Example: ['John Doe', 'Jane Doe']
     * @bodyParam country string Example: Nigeria
     * @bodyParam number_of_pages int Example: 1090
     * @bodyParam publisher string Example: Walt Disney
     * @bodyParam release_date string Example: 2022-01-01
     *
     * @response 200
     *
     * //if status_code === 200
     *
     * {
     * "status": "success",
     * "status_code": 200,
     * "data": {
     *          'name': string,
     *          'isbn': string,
     *          'authors': array,
     *          'number_of_pages': int,
     *          'publisher': string,
     *          'country': string,
     *          'release_date': date
     *       }
     * }
     *
     * //if status_code === 500
     *
     * {
     * "status": "fail",
     * "status_code": 500,
     * "data": {
     *      "message": string
     *  }
     * }
     *
     * @authenticated
     * @group Auth APIs
     * @subgroup Sample Require Token APIs
     * @subgroupDescription This is a Novel Management API for
     * testing the <b>require.token</b> middleware
     */
    public function updateNovel(Request $request, $id): JsonResponse
    {
        $request->merge(['id' => $id]);

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:novels',
            'name' => 'min:6|max:100',
            'isbn' => ['min:6', 'max:100',
                Rule::unique('novels')->where(function ($query) use($request) {
                    return $query->where('id', '<>', $request->id);
                })
            ],
            'authors' => 'min:6|max:1000',
            'country' => 'max:100',
            'number_of_pages' => 'digits_between:1,5',
            'publisher' => 'min:6|max:100',
            'release_date' => 'date',
        ]);

        if ($validator->fails()) {
            $data = (array) $validator->errors()->all();
            return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'), 500, $data);
        }

        if (Novel::where('id', $request->id)
                ->update((array) $validator
                        ->safe()
                        ->except('id'))
        ) {
            $data = Novel::find($request->id);
            return $this->httpJsonResponse(trans('tokenmiddleware::general.success'), 200, $data);
        }

        $data = ['message' => 'Novel could not be updated'];
        return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'), 500, $data);
    }

    /**
     * Delete novel.
     *
     * @urlParam id string required The ID of the novel Example: 1
     *
     * @response 200
     *
     * //if status_code === 200
     *
     * {
     * "status": "success",
     * "status_code": 200,
     * "data": {
     *          'name': string,
     *          'isbn': string,
     *          'authors': array,
     *          'number_of_pages': int,
     *          'publisher': string,
     *          'country': string,
     *          'release_date': date
     *       }
     * }
     *
     * //if status_code === 500
     *
     * {
     * "status": "fail",
     * "status_code": 500,
     * "data": {
     *      "message": string
     *  }
     * }
     *
     * @authenticated
     * @group Auth APIs
     * @subgroup Sample Require Token APIs
     * @subgroupDescription This is a Novel Management API for
     * testing the <b>require.token</b> middleware
     */
    public function deleteNovel(Request $request, $id): JsonResponse
    {
        if (Novel::where('id', $id)->delete()
        ) {
            $data = Novel::withTrashed()->find($id);
            return $this->httpJsonResponse(trans('tokenmiddleware::general.success'), 200, $data);
        }

        $data = ['message' => 'Novel could not be deleted'];
        return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'), 500, $data);
    }
}
