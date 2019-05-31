<li class="m-menu__item  {{Route::current()->getName() == 'user' ? 'm-menu__item--active' : ''}}" aria-haspopup="true">
    <a href="{{action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index')}}" class="m-menu__link ">
        <i class="m-menu__link-icon flaticon-users"></i>
        <span class="m-menu__link-title"> 
            <span class="m-menu__link-wrap"> 
                <span class="m-menu__link-text">
                    User
                </span>
             </span>
         </span>
     </a>
</li>