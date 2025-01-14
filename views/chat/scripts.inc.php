<script type="application/javascript">
    $(document).ready(function () {
        let base_url = "<?= $chat_backend_url ?>"
        let recipient_id = 0
        $("#new-chat-send").on('click', function () {
            let login = $('#new_chat_login').val();
            let message = $('#new_chat_message').val();
            if (!login || !message) {
                return
            }
            fetch(`${base_url}/message/`, {
                method: 'POST',
                headers: {
                    'Authorization': "<?= $user->getUserInfo()['ID'] ?>",
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    "recipient_login": login,
                    "message": message,
                })
            }).then(res => {
                return res.json();
            }).then(json => {
                if (!json.success) {
                    alert(json.error);
                } else {
                    document.location.reload();
                }
            });
        });


        fetch(`${base_url}/message/recipients`, {
            headers: {
                'Authorization': "<?= $user->getUserInfo()['ID'] ?>",
                'Content-Type': 'application/json'
            },
        })
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                data.result.map(item => {
                    $(".list-unstyled").append(
                        `<li id="${item.id}" class="user clearfix" name="${item.name}">
                         <div class="about">
                             <div class="name">${item.name}</div>
                         </div>
                      </li>
        `);
                });
            }).then(() => {

            $(".user").on('click', function () {
                recipient_id = $(this).attr('id');
                $('.chat-about-desc').html(`${$(this).attr('name')}`);
                $(".messageWithUser").html('<div style="width: 100%; height: 580px;" class="center">\n' +
                    '         <div class="spinner-border" role="status">\n' +
                    '             <span class="sr-only">Loading...</span>\n' +
                    '         </div>\n' +
                    '     </div>');
                fetch(`${base_url}/message/${recipient_id}`, {
                    headers: {
                        'Authorization': "<?= $user->getUserInfo()['ID'] ?>",
                        'Content-Type': 'application/json'
                    },
                })
                    .then((response) => {
                        $(".messageWithUser").html('');
                        return response.json();
                    })
                    .then((data) => {
                        data.result.map(item => {
                            $(".messageWithUser").append(
                                `<li class="clearfix"">
                             <div class="message-data ${(item.sender_id != $(this).attr('id')) ? 'text-right' : ''}">
                                 <span class="message-data-time">${item.date_send.replace('T', ' ')}</span>
                             </div>
                             <div class="message ${item.sender_id != $(this).attr('id') ? 'other-message float-right' : 'my-message'}">${item.message}</div>
                            </li>`
                            );
                        });
                        $('.chat-history').scrollTop($('.chat-history').height() * 10);
                    });
            });

            $('.form-control').on('keypress', function (e) {
                if (e.which === 13) {
                    if (!recipient_id){
                        alert('Сначала выберете пользователя!');
                        return
                    }
                    $(this).attr("disabled", "disabled");
                    $('.spin-modal').modal('show');
                    setTimeout(function () {
                        $('.spin-modal').modal('hide');
                    }, 1000);
                    fetch(`${base_url}/message/`, {
                        method: 'POST',
                        headers: {
                            'Authorization': "<?= $user->getUserInfo()['ID'] ?>",
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            "recipient_id": recipient_id,
                            "message": $(this).val(),
                        })
                    }).then(res => {
                        return res.json();
                    }).then(json => {
                        $(".messageWithUser").append(
                            `<li class="clearfix"">
                             <div class="message-data text-right">
                             </div>
                             <div class="message other-message float-right">${$(this).val()}</div>
                            </li>`
                        );
                        $(this).val('');
                        $('.chat-history').scrollTop($('.chat-history').height() * 10);
                    });
                    $(this).removeAttr("disabled");
                }
            });
        });
    });
</script>
