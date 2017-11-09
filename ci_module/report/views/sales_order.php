<script type="text/javascript">
    var base_url_template = "<?php echo site_url() ?>company/words/";

 function modal_content(template_id, template_name, template_image, status)
    {
        $('#template_id').val(template_id);
        if (status == "1")
        {
            $('#btn_use').addClass('disabled');
            $('#btn_use').html('Already in use');
        } else
        {
            $('#btn_use').removeClass('disabled');
            $('#btn_use').html('Use');
        }
        $('#myModalLabel').empty();
        $('#myModalLabel').html(template_name);
        $('#modal_template .modal-dialog .modal-content .modal-body').empty();
        $('#modal_template .modal-dialog .modal-content .modal-body').append('<img src="' + base_url_template + template_image + '" width="100%"/>');
    }

function get_templates()
    {
        $('#template_list').empty();
        $('#template_list').html('<img src="<?php echo site_url() ?>assets/images/loading.gif"/>Loading templates list...');
        $.ajax({
            type: "POST",
            url: "<?php echo site_url('report/report/get_templates_sales_order'); ?>",
            dataType: "json",
            success: function (data)
            {
                $('#template_list').empty();
                $.each(data.data, function (index, key) {
                    var html = "";

                    if (key.is_used == "1") {

                        var style = ' style="cursor:pointer; float:left; margin-left:5px; margin-right:5px; margin-top:5px; padding:5px; background-color:#ff9900;" ';
                    } else
                    {

                        var style = ' style="cursor:pointer; float:left; margin-left:5px; margin-right:5px; margin-top:5px; padding:5px;" ';
                    }

                    html += '<div data-toggle="modal" onClick="modal_content(\'' + key.id + '\',\'' + key.template_name + '\',\'' + key.template_screenshot + '\',\'' + key.is_used + '\')" data-target="#modal_template" class="template_pic" ' + style + '>';

                    html += '<img src="' + base_url_template + key.template_screenshot + '" width="150"/>';
                    html += '<div style="font-weight:bold; text-align:center;">' + key.template_name + '</div>';
                    html += '</div>'
                    $('#template_list').append(html);
                });

            }

        });
    }

 function use_template()
    {
        var template_id = $('input[id="template_id"]');
        var data = {
            'template_id' : template_id.val(),
        };
        $('#template_list').empty();
        $('#template_list').html('<img src="<?php echo site_url() ?>assets/images/loading.gif"/>Saving selected template...');
        $.ajax({
            type: "POST",
            url: "<?php echo site_url('report/report/use_template_sales_order'); ?>",
            dataType: "json",
            data: data,
            success: function (data)
            {
                get_templates();
            }
        });
    }

    function delete_template() {

        var template_id = $('input[id="template_id"]');
        var data = {
            'template_id' : template_id.val(),
        };

        $.ajax({
            type: "POST",
            url: "<?php echo site_url('report/report/delete_template_sales_order'); ?>",
            dataType: "json",
            data: data,
            success: function (data)
            {
                $('#modal_template').modal('hide');
                get_templates();
            }
        });
    }

function submitForm() {
        var datasend = new FormData();
        datasend.append('template_name', $("#template_name").val());
        datasend.append('file', $("#file")[0].files[0]);
        datasend.append('file2', $("#file2")[0].files[0]);

        $('#output').html('<img src="<?php echo site_url() ?>assets/images/loading.gif"/>&nbsp; Please wait while system updating your database');

        $.ajax({
            url: "<?php echo site_url("report/report/do_upload_sales_order")?>",
            type: "POST",
            data: datasend,
            processData: false,
            contentType: false
        }).done(function (data) {
            console.log(data);
            $('#output').text(data);
            if (data == "Upload Finished")
            {
                setTimeout(
                        function () {
                            get_templates();
                        },
                        100);
            }
        });
        return false;
    }


$(document).ready(function ()
    {
        get_templates();

        $('#btn_use').click(function ()
        {
            use_template();
            get_templates();

        });

        $('#btn_delete').click(function ()
        {
            delete_template();
            $('#modal_template').modal('hide');
            get_templates();

        });
    });

</script>

<input type="hidden" id="template_id" />
<div class="page-content">
    <div class="row">
        <div>
              
        </div>           
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="clearfix portlet light portlet-fit portlet-form">
                <div class="portlet-title">
                    <div class="caption font-green">
                        <span class="caption-subject bold uppercase"><h3>Upload new template</h3></span>
                    </div>
                </div>
            </div>
            <div class="form-body form-horizontal justify-content-center">
                <div class="pb-2">                                 
                    <form id="fileinfo" enctype="multipart/form-data">
                        <div class="row" style="margin-bottom:10px;"> 
                            <div class="col-lg-6">
                                <label>Template Name:</label><br>
                                <input class="form-control" type="text" name="template_name" id="template_name" required />
                            </div>
                        </div>
                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-lg-6">
                                <label>Select .docx file:</label><br>
                                <input class="btn btn-default" type="file" id="file" name="file" accept=".docx,.DOCX" required />
                            </div>
                            <div class="col-lg-6">
                                <label>Select screenshot file:</label><br>
                                <input class="btn btn-default" type="file" id="file2" name="file2" accept=".jpg,.JPG" required />
                            </div>
                        </div>
                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-lg-6"><input class="btn green btn_left" type="button" value="Upload Template" onclick="submitForm()" /></div>
                            <div class="col-lg-6"></div>
                        </div>
                        <div id="output">
                            
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="panel">
            <div class="col-md-12">
                <div class="clearfix portlet light portlet-fit portlet-form">
                    <div class="portlet-title">
                        <div class="caption font-green">
                            <span class="caption-subject bold uppercase"><h3>Templates for Words Document Generator</h3></span>
                        </div>
                    </div>
                </div>
                <div class="form-body form-horizontal justify-content-center">
                    <div class="pb-2">
                        <div id="template_list">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="panel">
            <div class="col-md-12">
                <div class="clearfix portlet light portlet-fit portlet-form">
                    <div class="portlet-title">
                        <div class="caption font-green">
                            <span class="caption-subject bold uppercase"><h3>Download Words Templates</h3></span>
                        </div>
                    </div>
                </div>
                <div class="panel-body" style="text-transform: uppercase;">
                    <div class="form-body form-horizontal justify-content-center">
                        <div class="pb-2">
                        <a href="<?php echo site_url() ?>company/public/template/tax_invoice_template.docx" >
                            <img src="<?php echo site_url() ?>assets/images/Microsoft_Word_logo.png" width="5%"/>
                            Word Template
                        </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    

<div class="modal fade" id="modal_template" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width:800px;">
        <div class="modal-content">
            <div class="modal-header">

                <button style="float:right;" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button style="float:right;" type="button" class="btn btn-danger" id="btn_delete">Delete</button>
                <button style="float:right;" type="button" id="btn_use" class="btn btn-primary disabled"  data-dismiss="modal"></button>
                <h4 class="modal-title" id="myModalLabel"></h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>