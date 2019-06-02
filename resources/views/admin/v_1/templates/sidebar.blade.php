<li class="m-menu__item  {{Route::current()->getName() == 'post' ? 'm-menu__item--active' : ''}}" aria-haspopup="true">
    <a href="{{action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\PostController@index')}}" class="m-menu__link ">
        <i class="m-menu__link-icon flaticon-notes"></i>
        <span class="m-menu__link-title"> 
            <span class="m-menu__link-wrap"> 
                <span class="m-menu__link-text">
                    Posts
                </span>
             </span>
         </span>
     </a>
</li>