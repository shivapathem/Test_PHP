<style>
#facebox {
    z-index: 100;
}
</style>
<form id="allocate-user-add-form" autocomplete="off" style="width: 560px;">
    <h2 class="form-heading"><b>Add New Allocate User</b></h2>
    <div style="background: #eee; border: 1px solid #666; margin-top:5px;">
        <div id="allocate-user-validation-errors"></div>
        <div style="display: flex;">
            <label for="allocate-user-add-netlogin-frm" style="width: 30%; font-size: 12px; border-right: 1px solid #000; padding: 4px; height: 25px;">
                Network Login <span style="color: red;" aria-label="required">*</span>
            </label>
            <div style="width: 67%; padding: 4px; height: 25px;">
                <input type="text" style="width: 97%;" id="allocate-user-add-netlogin-frm" name="allocate_user_add_netlogin_frm" size="50"required aria-required="true">
            </div>
        </div>
    </div>
    <p style="float: right; margin-top: 5px; margin-bottom: 0px;">
        <button type="button" class="allocate-user-add-form-button" id="submit-create-allocate-user-add-form" aria-label="Add new allocate-user-add">Add User</button>
    </p>
</form>