// File: assets/js/app.js (Versione Finale, Completa e Funzionante)
$(document).ready(function() {

    // --- FUNZIONI PER CREARE L'HTML ---
    function createCommentHtml(comment) {
        const canDelete = (currentUserID == comment.utente_id);
        const canReport = (currentUserID != comment.utente_id);
        let actionHtml = '';
        if (canDelete) {
            actionHtml = `<a href="#" class="action-link delete" data-id="${comment.id}">Elimina</a>`;
        } else if (canReport) {
            actionHtml = `<a href="#" class="action-link report" data-id="${comment.id}">Segnala</a>`;
        }
        const likeBtnClass = comment.user_liked ? 'comment-like-btn liked' : 'comment-like-btn';
        const heartIcon = comment.user_liked ? 'fa-heart' : 'fa-heart-o';
        let viewRepliesHtml = '';
        if (comment.reply_count > 0) {
            viewRepliesHtml = `<div class="mt-2 view-replies-wrapper"><a href="#" class="view-replies-btn" data-parent-id="${comment.id}"><i class="fa fa-level-down fa-rotate-90" style="margin-right: 5px;"></i>Visualizza ${comment.reply_count} risposte</a></div>`;
        }
        return `
            <div class="comment-container" id="comment-${comment.id}" data-comment-id="${comment.id}" data-author-id="${comment.utente_id}">
                <div class="comment-content-wrapper">
                    <div class="d-flex justify-content-between align-items-start p-2">
                        <div class="d-flex" style="flex-grow: 1; min-width: 0;">
                            <img src="${comment.foto_profilo_url}" alt="${comment.nome}" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover; flex-shrink: 0;">
                            <div style="min-width: 0;">
                                <div><strong>${comment.nome} ${comment.cognome}</strong><span class="ms-2 text-muted" style="font-size: 0.8em;">${comment.time_ago}</span></div>
                                <p class="mb-1">${comment.commento}</p>
                                <div class="comment-actions" style="font-size: 0.8em;">
                                    <a href="#" class="text-muted me-3 reply-to-comment-btn" data-parent-id="${comment.id}" data-username="${comment.nome} ${comment.cognome}">Rispondi</a>
                                    <span class="d-none d-md-inline">${actionHtml}</span>
                                </div>
                                ${viewRepliesHtml}
                            </div>
                        </div>
                        <div class="text-center" style="flex-shrink: 0; padding-left: 10px;">
                            <button class="${likeBtnClass}" data-comment-id="${comment.id}"><i class="fa ${heartIcon}"></i></button>
                            <div class="like-count" style="font-size: 0.8em;">${comment.like_count}</div>
                        </div>
                    </div>
                </div>
                <div class="replies-container"></div>
            </div>`;
    }
    function createReplyHtml(reply) {
        const canDelete = (currentUserID == reply.utente_id);
        const canReport = (currentUserID != reply.utente_id);
        let actionHtml = '';
        if(canDelete) {
            actionHtml = `<a href="#" class="action-link delete" data-id="${reply.id}">Elimina</a>`;
        } else if (canReport) {
            actionHtml = `<a href="#" class="action-link report" data-id="${reply.id}">Segnala</a>`;
        }
        const likeBtnClass = reply.user_liked ? 'comment-like-btn liked' : 'comment-like-btn';
        const heartIcon = reply.user_liked ? 'fa-heart' : 'fa-heart-o';
        const mainParentId = reply.parent_id;
        return `
            <div class="comment-container" id="comment-${reply.id}" data-comment-id="${reply.id}" data-author-id="${reply.utente_id}">
                <div class="comment-content-wrapper">
                    <div class="d-flex justify-content-between align-items-start p-2">
                        <div class="d-flex" style="flex-grow: 1; min-width: 0;">
                            <img src="${reply.foto_profilo_url}" alt="${reply.nome}" class="rounded-circle me-2" width="30" height="30" style="object-fit: cover; flex-shrink: 0;">
                            <div style="min-width: 0;">
                                <div><strong>${reply.nome} ${reply.cognome}</strong><span class="ms-2 text-muted" style="font-size: 0.8em;">${reply.time_ago}</span></div>
                                <p class="mb-1">${reply.commento}</p>
                                <div class="comment-actions" style="font-size: 0.8em;">
                                    <a href="#" class="text-muted me-3 reply-to-comment-btn" data-parent-id="${mainParentId}" data-username="${reply.nome} ${reply.cognome}">Rispondi</a>
                                    <span class="d-none d-md-inline">${actionHtml}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-center" style="flex-shrink: 0; padding-left: 10px;">
                            <button class="${likeBtnClass}" data-comment-id="${reply.id}"><i class="fa ${heartIcon}"></i></button>
                            <div class="like-count" style="font-size: 0.8em;">${reply.like_count}</div>
                        </div>
                    </div>
                </div>
            </div>`;
    }
    
function deleteComment(commentId) {
    if (confirm('Sei sicuro di voler eliminare questo commento?')) {
        
        // Troviamo l'ID del post a cui appartiene il commento
        const commentElement = $('#comment-' + commentId);
        const postContainer = commentElement.closest('.noticity-comment-wrapper');
        const postId = postContainer.attr('id').replace('comment-wrapper-', '');

        $.ajax({
            url: 'ajax/delete_comment.php',
            type: 'POST',
            // --- MODIFICA: Inviamo anche l'ID del post ---
            data: { 
                commento_id: commentId,
                post_id: postId 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Rimuoviamo il commento dalla vista
                    commentElement.fadeOut('slow', function() { $(this).remove(); });

                    // --- NUOVO: Aggiorniamo il contatore principale ---
                    const mainCounterElement = $(`.noticity-comment-btn[data-post-id='${postId}']`);
                    // La struttura del testo è "<i class='...'></i> NUMERO"
                    // Dobbiamo preservare l'icona e cambiare solo il numero.
                    mainCounterElement.html(`<i class="fa fa-comment"></i> ${response.new_comment_count}`);
                    
                } else {
                    alert('Errore: ' + response.error);
                }
            }
        });
    }
}
    


function reportComment(commentId) { $.ajax({ url: 'ajax/report_comment.php', type: 'POST', data: { commento_id: commentId }, dataType: 'json', success: function(response) { if (response.success) { alert('Grazie per la tua segnalazione.'); } else { alert('Errore: ' + response.error); } } }); }
    function fetchAndDisplayComments(postId, wrapperElement, offset = 0) { if(offset === 0) { wrapperElement.html('<p class="text-center">Caricamento...</p>'); } $.ajax({ url: 'ajax/fetch_comments.php', type: 'GET', data: { post_id: postId, limit: 5, offset: offset }, dataType: 'json', success: function(response) { if (offset === 0) { wrapperElement.empty(); let formHtml = `<form class="comment-form" data-post-id="${postId}"><div class="d-flex"><textarea name="commento" class="form-control me-2" rows="1" placeholder="Scrivi un commento..."></textarea><button type="submit" class="btn btn-primary">Invia</button></div></form><hr><div class="comments-list"></div><div class="load-more-container mt-3 text-center"></div>`; wrapperElement.append(formHtml); } if (response.success) { const commentsList = wrapperElement.find('.comments-list'); const loadMoreContainer = wrapperElement.find('.load-more-container'); if (response.comments.length > 0) { $.each(response.comments, function(index, comment) { commentsList.append(createCommentHtml(comment)); }); } else if (offset === 0) { commentsList.html('<p>Nessun commento.</p>'); } const currentCount = commentsList.children('.comment-container').length; loadMoreContainer.empty(); if (currentCount < response.total_comments) { const nextOffset = currentCount; const loadMoreBtnHtml = `<button class="btn btn-secondary btn-sm load-more-comments-btn" data-post-id="${postId}" data-offset="${nextOffset}">Carica altri</button>`; loadMoreContainer.append(loadMoreBtnHtml); } } } }); }
    

function fetchAndDisplayReplies(parentId, offset) {
    const repliesContainer = $(`#comment-${parentId}`).find('.replies-container');
    $.ajax({ 
        url: 'ajax/fetch_replies.php', 
        type: 'GET', 
        data: { parent_id: parentId, limit: 5, offset: offset }, 
        dataType: 'json', 
        success: function(response) { 
            if (response.success && response.replies.length > 0) {
                if (offset === 0) {
                    repliesContainer.empty();
                }

                $.each(response.replies, function(index, reply) { 
                    repliesContainer.append(createReplyHtml(reply)); 
                });
                
                const currentCount = repliesContainer.children('.comment-container').length;
                repliesContainer.find('.load-more-replies-btn, .close-replies-wrapper').remove();
                
                if (currentCount < response.total_replies) {
                    const nextOffset = currentCount;
                    const loadMoreBtnHtml = `<a href="#" class="view-replies-btn load-more-replies-btn" data-parent-id="${parentId}" data-offset="${nextOffset}"><i class="fa fa-level-down fa-rotate-90" style="margin-right: 5px;"></i>Carica altre risposte</a>`;
                    repliesContainer.append(loadMoreBtnHtml);
                }

                // --- QUESTA È LA RIGA MODIFICATA ---
                // Applichiamo gli stili direttamente qui per forzare il risultato
                const closeBtnHtml = `
                    <div class="close-replies-wrapper" style="text-align: center; margin-top: 15px;">
                        <a href="#" class="close-replies-btn" data-parent-id="${parentId}" style="font-size: 0.8em; color: #6c757d; font-weight: bold; text-decoration: none;">
                            Nascondi risposte
                        </a>
                    </div>`;
                repliesContainer.append(closeBtnHtml);
            }
        } 
    }); 
}
    
    // --- GESTORI EVENTI ---
    $('body').on('click', '.comment-actions .action-link', function(e){ e.preventDefault(); const btn = $(this); const commentId = btn.data('id'); if (btn.hasClass('delete')) { deleteComment(commentId); } else { reportComment(commentId); } });
    
    let startX, currentX = null, isSwiping = false, swipedElement = null;
    const swipeThreshold = 60; 
    $('body').on('touchstart', '.comment-content-wrapper', function(e) {
    if ($(window).width() > 768) return;

    // --- QUESTA È L'UNICA MODIFICA ---
    // Se il tocco è su un link (a), un pulsante (button) o un'icona (i),
    // interrompe lo swipe e lascia funzionare il click.
    if ($(e.target).is('a, button, i')) {
        isSwiping = false; // Assicura che lo swipe venga annullato
        return;
    }
    // --- FINE MODIFICA ---

    if (swipedElement && !swipedElement.is($(this))) { swipedElement.css('transform', 'translateX(0px)'); }
    startX = e.originalEvent.touches[0].clientX;
    isSwiping = true;
    swipedElement = $(this);
    $(this).css('transition', 'none');

    const parentContainer = $(this).closest('.comment-container');
    parentContainer.find('.swipe-actions-container').remove();

    const authorId = parentContainer.data('author-id');
    const commentId = parentContainer.data('comment-id');
    const canDelete = (currentUserID == authorId);
    const canReport = (currentUserID != authorId);
    let actionHtml = '';
    if (canDelete) {
        actionHtml = `<button class="action-btn delete" data-id="${commentId}">Elimina</button>`;
    } else if (canReport) {
        actionHtml = `<button class="action-btn report" data-id="${commentId}">Segnala</button>`;
    }
    if(actionHtml) {
        const actionContainer = $(`<div class="swipe-actions-container">${actionHtml}</div>`);
        parentContainer.prepend(actionContainer);
        const height = $(this).outerHeight();
        actionContainer.css({ 'height': height, 'top': 0, 'right': 0 });
    }
});
    $('body').on('touchmove', '.comment-content-wrapper', function(e) {
        if (!isSwiping || $(window).width() > 768) return;
        currentX = e.originalEvent.touches[0].clientX;
        let deltaX = startX - currentX;
        if (deltaX > 0) { $(this).css('transform', `translateX(-${deltaX}px)`); }
    });
    $('body').on('touchend', '.comment-content-wrapper', function(e) {
        if (!isSwiping || $(window).width() > 768) return;
        isSwiping = false;
        let deltaX = startX - (currentX || startX);
        const contentWrapper = $(this);
        contentWrapper.css('transition', 'transform 0.3s ease');
        const actionContainer = contentWrapper.siblings('.swipe-actions-container');
        const actionWidth = actionContainer.outerWidth();
        if (deltaX > swipeThreshold) {
            contentWrapper.css('transform', `translateX(-${actionWidth}px)`);
        } else {
            contentWrapper.css('transform', 'translateX(0px)');
            swipedElement = null;
            setTimeout(() => actionContainer.remove(), 300);
        }
        startX = null; currentX = null;
    });
    $('body').on('click', '.swipe-actions-container .action-btn', function() {
        const btn = $(this); const commentId = btn.data('id');
        if (btn.hasClass('delete')) { deleteComment(commentId); } 
        else { reportComment(commentId); }
    });
    $('body').on('click', function(e){
        if (swipedElement && !swipedElement.is(e.target) && swipedElement.has(e.target).length === 0) {
            swipedElement.css('transform', 'translateX(0px)');
            setTimeout(() => swipedElement.siblings('.swipe-actions-container').remove(), 300);
            swipedElement = null;
        }
    });
    
    $('body').on('click', '.noticity-comment-btn', function(e) { e.preventDefault(); const postId = $(this).data('post-id'); const commentWrapper = $('#comment-wrapper-' + postId); const isVisible = commentWrapper.is(':visible'); commentWrapper.slideToggle(); if (!isVisible && commentWrapper.children().length === 0) { fetchAndDisplayComments(postId, commentWrapper); } });
    $('body').on('click', '.view-replies-btn', function(e) { e.preventDefault(); const btn = $(this); const parentId = btn.data('parent-id'); const offset = btn.data('offset') || 0; btn.hide(); fetchAndDisplayReplies(parentId, offset); });
    $('body').on('click', '.load-more-comments-btn', function() { const btn = $(this); const postId = btn.data('post-id'); const offset = btn.data('offset'); const wrapperElement = btn.closest('.noticity-comment-wrapper'); btn.text('Caricamento...').prop('disabled', true); fetchAndDisplayComments(postId, wrapperElement, offset); });
    $('body').on('submit', '.comment-form, .reply-form', function(e) { e.preventDefault(); const form = $(this); const postId = form.closest('.noticity-comment-wrapper').attr('id').replace('comment-wrapper-', ''); const commentText = form.find('textarea[name="commento"]').val(); const parentIdInput = form.find('input[name="parent_id"]'); let parentId = null; if (parentIdInput.length > 0) { parentId = parentIdInput.val(); } if (commentText.trim() === '') { alert('Il commento non può essere vuoto.'); return; } $.ajax({ url: 'ajax/add_comment.php', type: 'POST', data: { post_id: postId, commento: commentText, parent_id: parentId }, dataType: 'json', success: function(response) { if (response.success) { if (parentId) { const newReplyHtml = createReplyHtml(response.comment); const repliesContainer = $(`#comment-${parentId}`).find('.replies-container'); repliesContainer.append(newReplyHtml); form.closest('.reply-form-wrapper').remove(); } else { const mainCommentWrapper = $('#comment-wrapper-' + postId); fetchAndDisplayComments(postId, mainCommentWrapper); } } else { alert('Errore: ' + response.error); } } }); });
    $('body').on('click', '.reply-to-comment-btn', function(e) { e.preventDefault(); const replyBtn = $(this); const parentId = replyBtn.data('parent-id'); const username = replyBtn.data('username'); const commentContainer = replyBtn.closest('.comment-container'); $('.reply-form-wrapper').remove(); const replyFormHtml = `<div class="reply-form-wrapper"><form class="reply-form"><input type="hidden" name="parent_id" value="${parentId}"><div class="d-flex"><textarea name="commento" class="form-control me-2" rows="1"></textarea><button type="submit" class="btn btn-sm btn-primary">Invia</button></div></form></div>`; commentContainer.append(replyFormHtml); const textarea = commentContainer.find('textarea'); textarea.attr('placeholder', `Rispondi a ${username}...`); textarea.val(`@${username} `); textarea.focus(); const textLength = textarea.val().length; textarea[0].setSelectionRange(textLength, textLength); });
    $('body').on('click', '.comment-like-btn', function() { const likeButton = $(this); const commentId = likeButton.data('comment-id'); $.ajax({ url: 'ajax/like_comment.php', type: 'POST', data: { commento_id: commentId }, dataType: 'json', success: function(response) { if (response.success) { likeButton.siblings('.like-count').text(response.like_count); const heartIcon = likeButton.find('.fa'); if (response.user_liked) { likeButton.addClass('liked'); heartIcon.removeClass('fa-heart-o').addClass('fa-heart'); } else { likeButton.removeClass('liked'); heartIcon.removeClass('fa-heart').addClass('fa-heart-o'); } } } }); });
    $('body').on('input', '.comment-form textarea, .reply-form textarea', function () { this.style.height = 'auto'; this.style.height = (this.scrollHeight) + 'px'; });


	// --- NUOVO: Gestione del CLICK su "CHIUDI RISPOSTE" ---
$('body').on('click', '.close-replies-btn', function(e) {
    e.preventDefault();
    const btn = $(this);
    const parentId = btn.data('parent-id');
    const commentContainer = $(`#comment-${parentId}`);
    const repliesContainer = commentContainer.find('.replies-container');

    // Svuota il contenitore delle risposte
    repliesContainer.empty();

    // Fa riapparire il link originale "Visualizza X risposte"
    commentContainer.find('.view-replies-btn').show();
});


});