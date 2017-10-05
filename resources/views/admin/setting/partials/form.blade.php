<div class='box-body'>
    <!-- Name -->
    <div class='row'>
        <div class="form-group {{ $errors->has('salesforce_dashboard_url') ? 'has-error' : '' }}">
            <label class="control-label col-lg-3" for="name">Salesforce Dashboard Url</label>
            <div class="col-lg-6">
                {!! Form::text('salesforce_dashboard_url',old('salesforce_dashboard_url'),array('class'=>'form-control')) !!}
                <span class="help-block">
                    <code>Please use "[CONTACT_ID]" where need to replace actual Contact ID</code>
                </span>
                {!! $errors->first('salesforce_dashboard_url', '<span class="help-block">:message</span>') !!}
            </div>
        </div>
        <div class="form-group {{ $errors->has('salesforce_application_page_url') ? 'has-error' : '' }}">
            <label class="control-label col-lg-3" for="name">Salesforce Application Page Url</label>
            <div class="col-lg-6">
                {!! Form::text('salesforce_application_page_url',old('salesforce_application_page_url'),array('class'=>'form-control')) !!}
                <span class="help-block">
                    <code>Please use "[CONTACT_ID]" where need to replace actual Contact ID</code>
                </span>
                {!! $errors->first('salesforce_application_page_url', '<span class="help-block">:message</span>') !!}
            </div>
        </div>
    </div>
</div>