<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController;

use Validator;
use Auth;

class CategoryController extends TaxonomyController
{
    protected $module = 'post';
    protected $mod_dir = 'Category';
    protected $taxonomy = 'category';

    public function store(Request $request)
    {
    	 $validator = Validator::make($request->all(), [
            'term.name' => 'required|max:191',
            'term.slug' => 'required|max:191',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        if($request->isMethod('POST'))
        {
            $data = $request->except('_token', '_method');
            $taxonomy = new $this->taxonomy_m;
        }
        else
        {
            $data = $request->except('_token', '_method', 'id');
            $taxonomy = $this->taxonomy_repository->findOrFail(decrypt($request->input('id')));
            $this->authorize('update-taxonomy', $taxonomy);
        }


        $term = $this->terms_repository->findBySlug($request->input('term.slug'));
        if(empty($term))
        {
        	$term = new $this->terms_m;
        	$term->name = $request->input('term.name');
        	$term->slug = $request->input('term.slug');
        	$term->created_by = Auth::id();
            $term->modified_by = Auth::id();
            $term->save();
        }

        $taxonomy->term_id = $term->id;
        $taxonomy->description = $request->input('taxonomy.description');
        $taxonomy->taxonomy = $request->input('taxonomy.taxonomy');

        if($request->isMethod('POST'))
        {
            $taxonomy->created_by = Auth::id();
            $taxonomy->modified_by = Auth::id();
        }
        else
        {
            $taxonomy->modified_by = Auth::id();
        }

        if($taxonomy->save())
        {
            if($request->isMethod('POST'))
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\CategoryController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add Category!'));
            }
            else
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\CategoryController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update Category!'));
            }
        }
        else
        {
            if($request->isMethod('POST'))
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Add Category!'));
            }
            else
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Update Category!'));
            }
        }
    }
}
