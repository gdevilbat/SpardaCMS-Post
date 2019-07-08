<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Foundation\AbstractTaxonomy;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy as Taxonomy_m;

use Validator;
use Auth;

class TagController extends AbstractTaxonomy
{
    public function __construct()
    {
        parent::__construct();
        $this->module = 'post';
        $this->mod_dir = 'Tag';
        $this->taxonomy = 'tag';

    }

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
            $data = $request->except('_token', '_method', \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey());
            $taxonomy = $this->taxonomy_repository->with('term')->findOrFail(decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey())));
            $this->authorize('update-taxonomy', $taxonomy);
        }

        if($request->isMethod('POST'))
        {
            $term = $this->terms_repository->findBySlug($request->input('term.slug'));
        }
        else
        {
            $term = $this->terms_repository->find($taxonomy->term->getKey());
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

        $taxonomy->term_id = $term->getKey();
        $taxonomy->description = $request->input('taxonomy.description');
        $taxonomy->taxonomy = $this->getTaxonomy();

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
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add Tag!'));
            }
            else
            {
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update Tag!'));
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
