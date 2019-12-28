/* UTILS */ 

function hasClass(element, className) { return element.classList.contains(className); }

function removeClass(element, className) { element.classList.remove(className); }

function addClass(element, className) { element.classList.add(className); }


/** FAQ */

function openAll(listElement) {
    var dtElements = listElement.getElementsByTagName("dt"); 
    for(var i = 0; i < dtElements.length; i++)
        openDT(dtElements[i]); 
}

function closeAll(listElement) {
    var dtElements = listElement.getElementsByTagName("dt"); 
    for(var i = 0; i < dtElements.length; i++)
        closeDT(dtElements[i]); 
}

function dtClick(dtElement) {
    if(hasClass(dtElement, "faq_closed"))
        openDT(dtElement); 
    else closeDT(dtElement); 
}

function openDT(dtElement) {
    removeClass(dtElement, "faq_closed"); 
    removeClass(dtElement.nextElementSibling, "hide"); 
}

function closeDT(dtElement) {
    addClass(dtElement, "faq_closed"); 
    addClass(dtElement.nextElementSibling, "hide"); 
}


function faq_init() {
    var faqDTs = document.querySelectorAll(".faq_list dt"); 
    for(var i = 0; i < faqDTs.length; i++) {
        faqDTs[i].addEventListener("click", (e) => dtClick(e.target)); 
        closeDT(faqDTs[i]); 
    }

    var closeControls = document.getElementsByClassName("faq_control_close"); 
    for(var i = 0; i < closeControls.length; i++) {
        closeControls[i].addEventListener("click", function(e) {
            e.preventDefault(); 
            closeAll( document.getElementById(e.target.getAttribute("href").substr(1) ) ); 
        });  
    }
    
    var openControls = document.getElementsByClassName("faq_control_open"); 
    for(var i = 0; i < openControls.length; i++) {
        openControls[i].addEventListener("click", function(e) {
            e.preventDefault(); 
            openAll( document.getElementById(e.target.getAttribute("href").substr(1) ) ); 
        });  
    }
}


/** GESTIONE DEI CONTROLLI SU CAMPI DI INPUT */
MSG_TYPES = {
    ERROR: -1, 
    WARNING: 0, 
    SUCCESS: 1
}; 

function createHTMLBox(msg, type) {
    var box = document.createElement("div");
    box.classList.add("msg_box"); 

    var msgClass = "success_box"; 
    if(type == MSG_TYPES.ERROR)
        msgClass = "error_box"; 
    else if(type == MSG_TYPES.WARNING)
        msgClass = "warning_box"; 

    box.classList.add(msgClass); 

    box.innerHTML = msg; 
    
    return box; 
}

function showAlertBox(element, msg) {
    var box = createHTMLBox(msg, MSG_TYPES.ERROR); 
    element.parentNode.insertBefore(box, element); 
}

function removePreviousBox(element) {
    var prevElement = element.previousElementSibling; 
    if(hasClass(prevElement, "msg_box"))
        prevElement.parentNode.removeChild(prevElement); 
}

function isEmail(email) {
    return new RegExp(/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/).test(email); 
}

function isPsw(psw) {
    return new RegExp(/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,}$/).test(psw); 
}

function isNotEmpty(str) { 
    return str && str.trim().length > 0; 
}

function has12Year(dateValue) {
    var today = new Date(); 
    var bDay = new Date(dateValue);
    
    // differenza rispetto al 1970 (unix timestamp)
    var diff =new Date(today - bDay); 
    return diff.getFullYear() >= 1970 + 12; 
}

function equals(str1, str2) {
    return str1.trim() == str2.trim(); 
}

/** NOTA: I CONTOLLI SONO NELLA SEGUENTE FORMA: 
 * 
 * { 
 *    idCampo0 => [
 *      [controllo0, messaggioDiErrore0], 
 *      ...
 *      [controlloN, messaggioDiErroreN], 
 *    ], 
 * 
 *    ...
 * }
 */

 // aggiunge ai vari field di input indicati in fields i relativi eventi e controlli 
function addFocusEvents(fields) {

     // per ogni campo 
     for( field in fields ) {

        var fieldElement = document.getElementById(field); 

        // quando il campo perde il focus vengono eseguiti i vari controlli 
        fieldElement.addEventListener("focusout", function(e) {
            removePreviousBox(e.target); 
            
            // controlli relativi al campo attuale 
            var fieldControls = fields[e.target.id]; 
            
            for(var i = 0; i < fieldControls.length; i++) {

                // mostro il messaggio di errore del primo controllo non rispettato 
                if(!fieldControls[i][0](e.target.value)) {
                    showAlertBox(e.target, fieldControls[i][1]); 
                    break; 
                }
            }
        } ); 
    }
}

// fa i controlli sui vari campi indicati in fields e ritorno TRUE sse i valori di TUTTI i campi rispettano i relativi controlli 
function executeControls(fields) {

    var allOK = true; 

    // per ogni campo 
    for( field in fields ) {

        // campo da controllare
        var currentField = document.getElementById(field); 

        // elimino eventuali errori 
        removePreviousBox(currentField); 

        // controlli relativi a quel campo 
        var currentFieldControls = fields[field]; 
        
        // per ogni controllo relativo a quel campo 
        for(var i = 0; i < currentFieldControls.length; i++) {

            // controllo se il campo lo rispetta, e in caso negativo mostro un messaggio di errore
            if(!currentFieldControls[i][0](currentField.value)) {
                allOK = false;
                showAlertBox(currentField, currentFieldControls[i][1]); 
                break; 
            } 
        }
    }

    // ritorno TRUE sse i valori di TUTTI i campi rispettano i relativi controlli 
    return allOK; 
}

/** GESTIONE DEI CONTROLLI SUL FORM DI LOGIN */
function login_init() {
    
    if(document.getElementById("login_form")) {

        var loginControls = {}; 
        loginControls["email"] = [ [isEmail, "Inserire una e-mail valida. "] ]; 
        loginControls["password"] = [ [isNotEmpty, "Inserire una password. " ] ];

        addFocusEvents(loginControls); 

        var loginBtn = document.getElementById("login_btn"); 
        loginBtn.addEventListener("click", (e) => { if(!executeControls(loginControls)) e.preventDefault();} );  
    }
}

/** GESTIONE DELLA PAGINA DI REGISTRAZIONE */
function imgPreview(sourceElement, previewElement) {
    if(sourceElement.files && sourceElement.files[0]) {
        var reader = new FileReader(); 
        reader.onload = function(ee) {
            previewElement.src = ee.target.result;
        }
        reader.readAsDataURL(sourceElement.files[0]);
    }
}

// estensioni valide per file
function isExtensionOK(filepath) {
    var extensions = ['png','jpg','jpeg'];
	return extensions.includes(filepath.split('.').pop());
}

// controlla che il file non superi la dimensione massima
function isSizeOK(fileSize) {
    return fileSize <= 5 * 1048576; // dimensione massima: 5MB
}

function isPIVA(piva) {
    return new RegExp(/^[0-9]{11}$/).test(piva); 
}

// una parola deve avere lunghezza maggiore o uguale a 4, ed essere composta solo da lettere
function isWord(word) {
    word = word.trim(); 
    return new RegExp(/^[a-zA-Z]/).test(word) && word.length >= 4; 
}

// controllo sulle foto 
function photoControl(photoField) {
    var ok = true; 

    if(isNotEmpty(photoField.value)) {
        removePreviousBox(photoField); 
        if(!isExtensionOK(photoField.value)) {
            showAlertBox(photoField, "L'estensione del file non appartiene a quelle permesse (png, jpg, jpeg)");
            ok = false; 
        } else if(!isSizeOK(photoField.files[0].size)) {    
            showAlertBox(photoField, "La dimensione del file supera la dimensione massima (5MB)"); 
            ok = false ;
        }
    }

    return ok; 
}

function reg_init() {

    if( document.getElementById("form_registrazione") ) {

        // preview dell'immagine
        var fileInput = document.getElementById("img_profilo"); 
        fileInput.addEventListener("change", function(e) {
            imgPreview(this, document.getElementById("img_profilo_preview"));
        }); 
        
        // controlli da applicare sempre
        var regControls = {}; 
        regControls["email"] = [ [isNotEmpty, "Inserire un'email."], [isEmail, "L'email inserita non è valida."]];
        regControls["nome"] = [ [isNotEmpty, "Inserire un nome."], [isWord, "Il nome può contenere solo lettere e deve essere lungo almeno 4 caratteri"]];
        regControls["cognome"] = [ [isNotEmpty, "Inserire un cognome."], [isWord, "Il cognome può contenere solo lettere e deve essere lungo almeno 4 caratteri"] ];
        regControls["password"] = [ [isNotEmpty, "Inserire una password."], [isPsw, "La password inserita non è valida. La password deve contentere almeno: <ul><li>8 caratteri ALFANUMERICI</li><li>1 lettera maiuscola</li><li>1 lettera minuscola</li><li>1 numero</li></ul>"]];
        regControls["nascita"] = [ [isNotEmpty, "Inserire una data di nascita."], [has12Year, "L'età minima per poter utilizzare questo sito è 12 anni."] ]        
        addFocusEvents(regControls); 

        // controlli più complessi 
        var repatPswField = document.getElementById("repeatpassword"); 
        repatPswField.addEventListener("focusout", function(e) { 
            removePreviousBox(e.target); 
            if(!equals(e.target.value, document.getElementById("password").value)) 
                showAlertBox(e.target, "Le due password non coincidono."); 
        }); 

        // controlli da applicare solo nel caso in cui l'utente sia un ristoratore  
        var ristoControls = {}; 
        ristoControls["piva"] = [ [isNotEmpty, "Inserire una partita iva"], [isPIVA, "La partita IVA inserita non è corretta."] ]; 
        ristoControls["rsoc"] = [ [isNotEmpty, "Inserire una ragione sociale"] ]; 
        addFocusEvents(ristoControls); 
        
        document.getElementById("reg_btn").addEventListener("click", (e) => { reg_btn_click(e, regControls, ristoControls); }); 
    }
}

function reg_btn_click(e, regControls, ristoControls) {

    // controllo che i campi della registrazione rispettino i controlli indicati
    var ok = executeControls(regControls); 
    
    // controlli solo se il tipo selezionato è ristoratore
    if( document.getElementById("ristoratore").checked )  
         ok = ok & executeControls(ristoControls); 

    // controlli sulle password 
    var repatPswField = document.getElementById("repeatpassword"); 
    if(!equals(repatPswField.value, document.getElementById("password").value)) {
        removePreviousBox(repatPswField); 
        showAlertBox(repatPswField, "Le due password non coincidono."); 
        ok = false; 
    }

    // controlli sulla foto di profilo 
    ok = ok & photoControl( document.getElementById("img_profilo") ); 
   
    if(!ok) e.preventDefault(); 
}


function modify_profile_init(){
 
    if(document.getElementById("edit_foto_profilo")) {
        document.getElementById("change_photo").addEventListener("click", function(e) {
            // controllo se l'immagine va bene
            if(!photoControl(document.getElementById("new_foto_profilo"))) 
                e.preventDefault(); 
        }); 
    }

    if(document.getElementById("edit_psw_data")) {
        
        //  controlli su password vecchia e nuova  
        var pswControls = {}; 
        pswControls["old_password"] = [ [isNotEmpty, "Inserisci la tua password attuale."] ];
        pswControls["password"] = [ [isNotEmpty, "Inserire una password."], [isPsw, "La password inserita non è valida. La password deve contentere almeno: <ul><li>8 caratteri ALFANUMERICI</li><li>1 lettera maiuscola</li><li>1 lettera minuscola</li><li>1 numero</li></ul>"]];
        addFocusEvents(pswControls); 

        // controllo che le due password coincidano 
        var repatPswField = document.getElementById("repeat_pwd"); 
        repatPswField.addEventListener("focusout", function(e) { 
            removePreviousBox(e.target); 
            if(!equals(e.target.value, document.getElementById("password").value)) 
                showAlertBox(e.target, "Le due password non coincidono."); 
        }); 

        // gestione del submit del form 
        document.getElementById("change_psw_btn").addEventListener("click", (e) => edit_psw_click(e, pswControls)); 
    }

    if(document.getElementById("edit_personal_data")) {
        var modifyControls = {}; 
        modifyControls["nome"] = [ [isNotEmpty, "Inserire un nome."], [isWord, "Il nome può contenere solo lettere e deve essere lungo almeno 4 caratteri"]];
        modifyControls["cognome"] = [ [isNotEmpty, "Inserire un cognome."], [isWord, "Il cognome può contenere solo lettere e deve essere lungo almeno 4 caratteri"] ];   
        modifyControls["piva"] = [ [isNotEmpty, "Inserire una partita iva"], [isPIVA, "La partita IVA inserita non è corretta."]]; 
        modifyControls["rsoc"] = [ [isNotEmpty, "Inserire una ragione sociale"] ]; 
        addFocusEvents(modifyControls); 
        
        document.getElementById("modify_profile_btn").addEventListener("click", (e) => { if(!executeControls(modifyControls)) e.preventDefault(); });
    }
}

function edit_psw_click(e, pswControls) {

    var ok = executeControls(pswControls);  

    var repatPswField = document.getElementById("repeat_pwd"); 
    if(!equals(repatPswField.value, document.getElementById("password").value)) {
        removePreviousBox(repatPswField); 
        ok = false; 
        showAlertBox(repatPswField, "Le due password non coincidono."); 
    }

    if(!ok) 
        e.preventDefault();
}

/*** INDEX */
function init_index() {
    if(document.getElementById("form_ricerca")) {

        var searchField = document.getElementById("search"); 

        // quando perde il focus
        searchField.addEventListener("focusout", function(e) {
            // se rispetta i controlli ed era presente un box di errore, lo rimuove 
            if(searchField.value.trim().length > 0) {
                removePreviousBox(searchField); 
            }
        }); 

        document.getElementById("search_btn").addEventListener("click", function(e) {
            // mostra un messaggio di errore se non è presente alcun valore da cercare
            if(searchField.value.trim().length == 0) {
                showAlertBox(searchField, "E' necessario inserire un valore da cercare");
                e.preventDefault();
            }            
        }); 
    }
}

function hasLengthBetween(string, min, max) {
    var stringL = string.trim().length; 
    return stringL >= min && stringL <= max;  
}

// un titolo deve contenere tra i 25 e 50 caratteri; 
function isTitle(titolo) { 
    return hasLengthBetween(titolo, 25, 50); 
}

// una recensione deve avere tra i 100 e 250 caratteri 
function isReview(review) {
    return hasLengthBetween(review, 100, 250); 
}

function init_ins_recensione() {
    if(document.getElementById("new_review_form")) {
      
        var reviewControls = {}; 
        reviewControls["titolo_recensione"] = [ [isNotEmpty, "Inserire un titolo per la recensione."], [isTitle, "Il titolo deve avere tra i 25 e 50 cartteri."]]; 
        reviewControls["contenuto_recensione"] = [ [isNotEmpty, "Inserire un contenuto alla recensione."], [isReview, "Il contenuto della recensione deve avere tra i 100 e 250 caratteri."]]; 
        addFocusEvents(reviewControls); 

        document.getElementById("send_review").addEventListener("click", (e) => { if(!executeControls(reviewControls)) e.preventDefault();})
    }

}

function init_ins_risto() {


}

window.onload = function() {

    // ----------- FAQ  ---------------
    faq_init(); 

    // ------------- LOGIN ---------------
    login_init();

    // ---------- REGISTRAZIONE -------------- 
    reg_init(); 

    // ----------- PROFILO ---------------
    modify_profile_init(); 

    // ---------- INDEX -------------
    init_index();

    // --------- INSERIMENTO RECENSIONE -------
    init_ins_recensione(); 

    // ---------- INSERIMENTO RISTORANTE -------
    init_ins_risto(); 
}; 
    
