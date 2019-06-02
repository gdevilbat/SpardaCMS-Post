<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Entities;

use Illuminate\Database\Eloquent\Model;

class TermRelationship extends Model
{
    protected $fillable = [];
    protected $table = 'term_relationships';

    public function taxonomy()
    {
    	return $this->belongsTo('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy', 'term_taxonomy_id');
    }
}
