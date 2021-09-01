<div class="col-12 px-0">
    <div class="m-portlet m-portlet--tab">
        <!--begin::Form-->
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon m--hide">
                            <i class="fa fa-gear"></i>
                        </span>
                        <h3 class="m-portlet__head-text">
                            Options
                        </h3>
                    </div>
                </div>
            </div>
            <div class="m-portlet__body px-0">
                <div class="form-group m-form__group d-flex px-0 flex-wrap">
                    <div class="col-7 d-flex">
                        <label for="exampleInputEmail1">Publish {{trans_choice('post::messages.post', 1)}}<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                    </div>
                    <div class="col-5">
                        <span class="m-switch m-switch--icon m-switch--danger">
                            <label>
                                <input type="checkbox" {{old('post.post_status') ? 'checked' : ((!empty($post) && $post->post_status == 'publish' ? 'checked' : ''))}} name="post[post_status]">
                                <span></span>
                            </label>
                        </span>
                    </div>
                </div>
                <div class="form-group m-form__group d-flex px-0 flex-wrap">
                    <div class="col-7 d-flex">
                        <label for="exampleInputEmail1">Open Comment<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                    </div>
                    <div class="col-5">
                        <span class="m-switch m-switch--icon m-switch--danger">
                            <label>
                                <input type="checkbox" {{old('post.comment_status') ? 'checked' : ((!empty($post) && $post->comment_status == 'close' ? '' : 'checked'))}} name="post[comment_status]">
                                <span></span>
                            </label>
                        </span>
                    </div>
                </div>
            </div>
        <!--end::Form-->
    </div>
</div>
<div class="col-12 px-0">
    <div class="m-portlet m-portlet--tab">
        <!--begin::Form-->
            <div class="m-portlet__body px-0">
                <div class="form-group m-form__group d-flex px-0 flex-wrap">
                    <div class="col-12 d-flex py-3">
                        <label for="exampleInputEmail1">Cover Image</label>
                    </div>
                    <div class="col-12">
                        <div class="fileinput fileinput-new" data-provides="fileinput">
                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                @if(!empty($post) && !empty($post->postMeta->where('meta_key', 'cover_image')->first()) && $post->postMeta->where('meta_key', 'cover_image')->first()->meta_value['file'] != null)
                                    <img src="{{generate_storage_url($post->postMeta->where('meta_key', 'cover_image')->first()->meta_value['file'])}}" class="img-fluid" alt=""> 
                                @else
                                    <img src="https://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" class="img-fluid" alt=""> 
                                @endif
                            </div>
                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"> </div>
                            <div>
                                <span class="btn btn-file btn-accent m-btn m-btn--air m-btn--custom">
                                    <span class="fileinput-new"> Select image </span>
                                    <span class="fileinput-exists"> Change </span>
                                    <input type="file" name="meta[cover_image][file]"> </span>
                                <a href="javascript:;" class="btn default fileinput-exists" data-dismiss="fileinput"> Remove </a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group m-form__group d-flex px-0 flex-wrap">
                        <div class="col-12 d-flex py-3">
                            <label for="exampleInputEmail1">Caption Image</label>
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control m-input count-textarea" placeholder="Cover Caption" name="meta[cover_image][caption]" data-target-count-text="#caption-cover" value="{{old('meta.cover_image.caption') ? old('meta.cover_image.caption') : (!empty($post) && $post->postMeta->where('meta_key', 'cover_image')->first() ? $post->postMeta->where('meta_key', 'cover_image')->first()->meta_value['caption'] : '')}}">
                            <div class="pt-1"><span id="caption-cover"></span> Character</div>
                        </div>
                    </div>
                </div>
            </div>
        <!--end::Form-->
    </div>
</div>
<div class="col-12 px-0">
    <div class="m-portlet m-portlet--tab">
        <!--begin::Form-->
            <div class="m-portlet__body px-0">
                <div class="form-group m-form__group d-flex px-0 flex-wrap">
                    <div class="col-12 d-flex py-3">
                        <label for="exampleInputEmail1">Meta Title</label>
                    </div>
                    <div class="col-12">
                        <input type="text" class="form-control m-input count-textarea" placeholder="Meta Title" name="meta[meta_title]" data-target-count-text="#meta-title" value="{{old('meta.meta_title') ? old('meta.meta_title') : (!empty($post) && $post->postMeta->where('meta_key', 'meta_title')->first() ? $post->postMeta->where('meta_key', 'meta_title')->first()->meta_value : '')}}">
                        <div class="pt-1"><span id="meta-title"></span> Character</div>
                    </div>
                </div>
                <div class="form-group m-form__group d-flex px-0 flex-wrap">
                    <div class="col-12 d-flex py-3">
                        <label for="exampleInputEmail1">Meta Keyword</label>
                    </div>
                    <div class="col-12">
                        <input type="text" class="form-control m-input" placeholder="Meta Keyword" name="meta[meta_keyword]" value="{{old('meta.meta_keyword') ? old('meta.meta_keyword') : (!empty($post) && $post->postMeta->where('meta_key', 'meta_keyword')->first() ? $post->postMeta->where('meta_key', 'meta_keyword')->first()->meta_value : '')}}" data-role="tagsinput">
                    </div>
                </div>
                <div class="form-group m-form__group d-flex px-0 flex-wrap">
                    <div class="col-12 d-flex py-3">
                        <label for="exampleInputEmail1">Meta Description</label>
                    </div>
                    <div class="col-12">
                        <textarea class="form-control m-input autosize count-textarea" placeholder="Meta Description" name="meta[meta_description]" data-target-count-text="#meta-description">{{old('meta.meta_description') ? old('meta.meta_description') : (!empty($post) && $post->postMeta->where('meta_key', 'meta_description')->first() ? $post->postMeta->where('meta_key', 'meta_description')->first()->meta_value : '')}}</textarea>
                        <div class="pt-1"><span id="meta-description"></span> Character</div>
                    </div>
                </div>
            </div>
        <!--end::Form-->
    </div>
</div>
{{--<div class="col-12 px-0">
    <div class="m-portlet m-portlet--tab">
        <!--begin::Form-->
            <div class="m-portlet__body px-0">
                <div class="form-group m-form__group d-flex px-0 flex-wrap">
                    <div class="col-12 d-flex py-3">
                        <label for="exampleInputEmail1">Parent</label>
                    </div>
                    <div class="col-12">
                        <select name="post[post_parent]" class="form-control m-input m-input--solid">
                            <option value="" selected>-- Non Group --</option>
                            @foreach ($parents as $parent)
                                <option value="{{$parent->getKey()}}" {{old('post.post_parent') && old('post.post_parent') == $parent->getKey() ? 'selected' : (!empty($post) && $post->post_parent == $parent->getKey() ? 'selected' : '')}}>-- {{ucfirst($parent->post_title)}} --</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group m-form__group d-flex px-0 flex-wrap">
                    <div class="col-12 d-flex py-3">
                        <label for="exampleInputEmail1">Menu Order</label>
                    </div>
                    <div class="col-12">
                        <input type="number" class="form-control m-input" name="post[menu_order]" min="0" value="{{old('post.menu_order') ? old('post.menu_order') : (!empty($post) ? $post->menu_order : 0)}}" placeholder="Menu Order">
                    </div>
                </div>
            </div>
        <!--end::Form-->
    </div>
</div>
--}}