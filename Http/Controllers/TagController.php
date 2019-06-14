<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController;

use Validator;
use Auth;

class TagController extends TaxonomyController
{
    protected $module = 'post';
    protected $mod_dir = 'Tag';
    protected $taxonomy = 'tag';

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
            $taxonomy = $this->taxonomy_repository->with('term')->findOrFail(decrypt($request->input('id')));
            $this->authorize('update-taxonomy', $taxonomy);
        }

        if($request->isMethod('POST'))
        {
            $term = $this->terms_repository->findBySlug($request->input('term.slug'));
        }
        else
        {
            $term = $this->terms_repository->find($taxonomy->term->id);
        }

        if(empty($term))
        {
            $term = new $this->terms_m;
            $term->created_by = Auth::id();
        }
        
        $term->name = $request->input('term.name');
        $term->slug = $request->input('term.slug');
        $term->modified_by = Auth::id();
        $term->save();

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
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\TagController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add Tag!'));
            }
            else
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\TagController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update Tag!'));
            }
        }
        else
        {
            if($request->isMethod('POST'))
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Add Tag!'));
            }
            else
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Update Tag!'));
            }
        }
    }
}
