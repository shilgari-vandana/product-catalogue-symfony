$(function() {
    var usernameEl = $('#username');
    var passwordEl = $('#password');

    // in a real application, the user/password should never be hardcoded
    // but for the demo application it's very convenient to do so
    if (!usernameEl.val() || 'vandana_admin' === usernameEl.val()) {
        usernameEl.val('vandana_admin');
        passwordEl.val('vandana@admin');
    }
});
