$(document).ready(function() {
    
        const $tbody = $('.data-table tbody');
        const $filterLinks = $('.filters a');

        $filterLinks.on('click', function (e) {
            e.preventDefault();

        //set active class
            $filterLinks.removeClass('active');
            $(this).addClass('active');

        //Extract filter from href
            const url = new URL($(this).attr('href'), window.location.origin);
            const filter = url.searchParams.get('filter') || 'all';

        //AJAX request
            $.ajax({
              url: 'index.php',
              method: 'GET',
              data: {
                ajax: 1,
                filter: filter
            },
              dataType: 'json',
              success: function (contacts) {
                $tbody.empty();

                if (!contacts.length) {
                    $tbody.append(
                        '<tr><td colspan="5">No Contacts Found</td></tr>'
                    );
                    return;
                }

                contacts.forEach(contact => {
                    $tbody.append(`
                       <tr>
                            <td>${contact.title}. ${contact.firstname} ${contact.lastname}</td>
                            <td>${contact.email}</td>
                            <td>${contact.company}</td>
                            <td><span class="badge">${contact.type}</span></td>
                            <td>
                                 <a href="view_contact.php?id=${contact.contact_id}">
                                     View
                                 </a>
                            </td>
                        </tr>
                    `);
                    
                
                });
              },
              error: function () {
                $tbody.html(
                    '<tr><td colspan="5">Error loading contacts</td></tr>');
              }
           });
        });

        



        $(document).on('submit', '#userForm', function (e) {
            e.preventDefault();
            $('#main-content').load('users.php?ajax=1', $(this).serialize());

        });

    });

        //Assign or switch contact
        $(document).on('submit', '#contactActionsForm', function (e) {
            e.preventDefault();

            $.post(
                'view_contact.php?id=' + getContactId() + '&ajax=1',
                $(this).serialize(),
                function (data) {
                    if (data.success) reloadContactDetails();
                        
                },
                'json'
            );
        });

       //Add note
       $(document).on('submit', '#noteForm', function (e) {
            e.preventDefault();

            $.post(
                'view_contact.php?id=' + getContactId() + '&ajax=1',
                $(this).serialize(),
                function (data) {
                    if (data.success) 
                reloadContactDetails();
            
        },
        'json'
    );
});

        $(document).on('click', 'a[href^="view_contact.php"]', function (e) {
            e.preventDefault();

            const url = $(this).attr('href');

            $('#main-content').load(url + ' #main-content > *');
            history.pushState(null, '', url);
        });

        $(document).on('submit', '.contact-form', function (e) {
            e.preventDefault();
            const $form = $(this);
            $form.find('.form-message').remove();

            $.ajax({
                url: 'new_contact.php?ajax=1',
                method: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        $form.prepend('<p class="error-message form-message">' + response.error + '</p>');
                    } else if (response.success) {
                        $form[0].reset();
                        $form.prepend('<p class="success-message form-message">' + response.success + '</p>');

                    }
                },
                error: function() {
                    $form.prepend('<p class="error-message form-message">An unexpected error occured.</p>');
                }
            });
        });

function getContactId() {
    return new URLSearchParams(window.location.search).get('id');
}

function reloadContactDetails() {
    $('#contactDetails').load(
        'view_contact.php?id=' + getContactId() + ' #contactDetails > *'
    );
}

$(document).on('click', 'a[href^="new_contact.php"]', function (e) {
            e.preventDefault();

            const url = $(this).attr('href');

            $('#main-content').load(url + ' #main-content > *');
            history.pushState(null, '', url);
        });