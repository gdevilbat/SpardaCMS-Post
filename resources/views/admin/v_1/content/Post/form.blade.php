@extends('core::admin.'.$theme_cms->value.'.templates.parent')

@section('page_level_css')
    {{Html::style(module_asset_url('core:assets/metronic-v5/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css'))}}
    {{Html::style(module_asset_url('core:assets/metronic-v5/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css'))}}
@endsection

@section('title_dashboard', 'Post')

@section('breadcrumb')
        <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
            <li class="m-nav__item m-nav__item--home">
                <a href="#" class="m-nav__link m-nav__link--icon">
                    <i class="m-nav__link-icon la la-home"></i>
                </a>
            </li>
            <li class="m-nav__separator">-</li>
            <li class="m-nav__item">
                <a href="" class="m-nav__link">
                    <span class="m-nav__link-text">Home</span>
                </a>
            </li>
            <li class="m-nav__separator">-</li>
            <li class="m-nav__item">
                <a href="" class="m-nav__link">
                    <span class="m-nav__link-text">Post</span>
                </a>
            </li>
        </ul>
@endsection

@section('content')

<div class="row">
    <div class="col-sm-12">

        <form class="m-form m-form--fit m-form--label-align-right" action="{{action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\PostController@store')}}" method="post" enctype="multipart/form-data">
            <!--begin::Portlet-->
            <div class="row">
                <div class="col-md-8">
                    <div class="m-portlet m-portlet--last m-portlet--head-lg m-portlet--responsive-mobile" id="main_portlet">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-wrapper">
                                <div class="m-portlet__head-caption">
                                    <div class="m-portlet__head-title">
                                        <h3 class="m-portlet__head-text">
                                            Post Form
                                        </h3>
                                    </div>
                                </div>
                                <div class="m-portlet__head-tools">
                                    <div class="row justify-content-end">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--begin::Form-->
                            <div class="m-portlet__body">
                                <div class="col-md-9 offset-md-3">
                                    @if (!empty(session('global_message')))
                                        <div class="alert {{session('global_message')['status'] == 200 ? 'alert-info' : 'alert-warning' }}">
                                            {{session('global_message')['message']}}
                                        </div>
                                    @endif
                                    @if (count($errors) > 0)
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group m-form__group d-flex px-0">
                                    <div class="col-3 d-flex justify-content-end py-3">
                                        <label for="exampleInputEmail1">Post Title<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" class="form-control m-input slugify" data-target="slug" placeholder="Post Title" name="post[post_title]" value="{{old('post.post_title') ? old('post.post_title') : (!empty($post) ? $post->post_title : '')}}">
                                    </div>
                                </div>
                                <div class="form-group m-form__group d-flex px-0">
                                    <div class="col-3 d-flex justify-content-end py-3">
                                        <label for="exampleInputEmail1">Post Slug<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" class="form-control m-input" id="slug" placeholder="Post Slug" name="post[post_slug]" value="{{old('post.post_slug') ? old('post.post_slug') : (!empty($post) ? $post->post_slug : '')}}">
                                    </div>
                                </div>
                                <div class="form-group m-form__group d-flex px-0 flex-wrap">
                                    <div class="col-3 d-flex justify-content-end py-3">
                                        <label for="exampleInputEmail1">Category</label>
                                    </div>
                                    <div class="col">
                                        <select class="form-control m-input select2" name="taxonomy[category][]">
                                            @foreach ($categories as $category)
                                                <option value="{{$category->getKey()}}" {{!empty($post->taxonomies) && in_array($category->getKey(), $post->taxonomies->pluck(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey())->toArray()) ? 'selected' : ''}}>{{$category->term->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group m-form__group d-flex px-0 flex-wrap">
                                    <div class="col-3 d-flex justify-content-end py-3">
                                        <label for="exampleInputEmail1">Tag</label>
                                    </div>
                                    <div class="col">
                                        <select class="form-control m-input select2" name="taxonomy[tag][]" multiple>
                                            @foreach ($tags as $tag)
                                                <option value="{{$tag->getKey()}}" {{!empty($post->taxonomies) && in_array($tag->getKey(), $post->taxonomies->pluck(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey())->toArray()) ? 'selected' : ''}}>{{$tag->term->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group m-form__group d-flex px-0">
                                    <div class="col-12">
                                        <textarea class="form-control m-input texteditor" placeholder="Post Content" name="post[post_content]">{{old('post.post_content') ? old('post.post_content') : (!empty($post) ? $post->post_content : '')}}</textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="post[post_excerpt]" value="{{old('post.post_excerpt') ? old('post.post_excerpt') : (!empty($post) ? $post->post_excerpt : '')}}">
                            </div>
                            {{csrf_field()}}
                            @if(isset($_GET['code']))
                                <input type="hidden" name="{{\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey()}}" value="{{$_GET['code']}}">
                            @endif
                            {{$method}}

                        <!--end::Form-->
                    </div>
                </div>
                <div class="col-md-4">
                    @include('post::admin.v_1.partials.meta_data')
                </div>
            </div>
            <!--end::Portlet-->
        </form>

    </div>
</div>
{{-- End of Row --}}

@endsection

@section('page_level_js')
    {{Html::script(module_asset_url('core:assets/js/autosize.min.js'))}}
    {{Html::script(module_asset_url('core:assets/js/slugify.js'))}}
    {{Html::script(module_asset_url('core:assets/metronic-v5/global/plugins/ckeditor_4/ckeditor.js'))}}
    {{Html::script(module_asset_url('core:assets/metronic-v5/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js'))}}
    {{Html::script(module_asset_url('core:assets/metronic-v5/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js'))}}
@endsection