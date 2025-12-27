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

                if (contacts.length === 0) {
                    $tbody.append(
                        '<tr><td colspan="5">No Contacts Found</td></tr>'
                    );
                    return;
                }

                contacts.forEach(contact => {
                    const row = `
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
                    `;
                    $tbody.append(row);
                
                });
            },
             error: function () {
                $tbody.html(
                    '<tr><td colspan="5">Error loading contacts</td></tr>'
                );
            }
        });
    });
});

$(document).ready(function () {
    const $main = $('#main-content');

    
    $(document).on('submit', '#userForm', function (e) {
        e.preventDefault();

        $.post('users.php?ajax=1', $(this).serialize(), function (html) {
            $main.html(html);
        });

    });
});
