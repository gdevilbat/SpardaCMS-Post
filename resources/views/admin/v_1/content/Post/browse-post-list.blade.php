<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap 4 Website Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
  <style>
  .fakeimg {
    height: 200px;
    background: #aaa;
  }
  </style>
</head>
{{Html::style(module_asset_url('core:assets/metronic-v5/global/plugins/select2/css/select2.min.css'))}}
{{Html::style(module_asset_url('core:assets/metronic-v5/global/plugins/select2/css/select2-bootstrap.min.css'))}}
<body>

<div class="container" style="margin-top:30px">
  <div class="row">
    <div class="col-12">
      <form action="#">
          <div class="form-group">
            <label for="email">Select Post</label>
            <select name="post" class="form-control" id="post-list" required>
                <option selected disabled>Select Linked Post</option>
                @foreach ( $posts as $post)
                    <option data-id="{{$post->getKey()}}" data-url="{{url($post->created_at->format('Y').'/'.$post->created_at->format('m').'/'.$post->post_slug.'.html')}}">{{$post->post_title}}</option>
                @endforeach
            </select>
          </div>
          <button type="button" class="btn btn-primary float-right" onclick="addLink()">Submit</button>
        </form>
    </div>
  </div>
</div>
{{Html::script(module_asset_url('core:assets/metronic-v5/global/plugins/select2/js/select2.min.js'))}}
<script>
  $(document).ready(function() {
    $("#post-list").select2();
  });
    // Helper function to get parameters from the query string.
    function getUrlParam( paramName ) {
        var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' );
        var match = window.location.search.match( reParam );

        return ( match && match.length > 1 ) ? match[1] : null;
    }

    function addLink() {
        if($('select').eq(0).val() == null)
        {
            alert('Please Select One');
            return false;
        }
        
        var funcNum = getUrlParam( 'CKEditorFuncNum' );
        var fileUrl = 'http://c.cksource.com/a/1/img/sample.jpg';
        window.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl, function() {
            // Get the reference to a dialog window.
            var dialog = this.getDialog();
            // Check if this is the Image Properties dialog window.
            // Get the reference to a text field that stores the "alt" attribute.
            
            /*var element = dialog.getContentElement( 'tab-basic', 'data-url' );
            if ( element )
                element.setValue($('select').eq(0).children('option:selected').data('url'));*/

            var element = dialog.getContentElement( 'tab-basic', 'data-id' );
            if ( element )
                element.setValue($('select').eq(0).children('option:selected').data('id'));

            var element = dialog.getContentElement( 'tab-basic', 'text' );
            if ( element )
                element.setValue('Baca Juga: '+$('select').eq(0).val());
            // Return "false" to stop further execution. In such case CKEditor will ignore the second argument ("fileUrl")
            // and the "onSelect" function assigned to the button that called the file manager (if defined).
            // return false;
        } );
        window.close();
    }
</script>

</body>
</html>
