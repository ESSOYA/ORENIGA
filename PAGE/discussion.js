// Fonction pour liker un message
function likeMessage(messageId) {
    fetch("discussion.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `like=1&message_id=${messageId}`
    }).then(response => response.text())
      .then(data => {
          // Mettez à jour le nombre de likes dans l'interface
          location.reload(); // Ou bien rafraîchir seulement le compteur de likes
      });
}

// Fonction pour disliker un message
function dislikeMessage(messageId) {
    fetch("discussion.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `dislike=1&message_id=${messageId}`
    }).then(response => response.text())
      .then(data => {
          // Mettez à jour le nombre de dislikes dans l'interface
          location.reload(); // Ou bien rafraîchir seulement le compteur de dislikes
      });
}

// Fonction pour supprimer un message
function deleteMessage(messageId) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce message ?")) {
        fetch("discussion.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `delete=1&message_id=${messageId}`
        }).then(response => response.text())
          .then(data => {
              // Supprime le message de l'interface sans rafraîchir toute la page
              document.getElementById(`likeForm-${messageId}`).remove();
          });
    }
}

// Fonction pour envoyer une réponse
function sendResponse(messageId) {
    const responseText = document.getElementById(`reponse-${messageId}`).value;
    fetch("discussion.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `reponse=${responseText}&message_id=${messageId}`
    }).then(response => response.text())
      .then(data => {
          // Ajouter la réponse sans recharger la page
          location.reload(); // Ou ajouter la réponse dynamiquement à l'interface
      });
}
