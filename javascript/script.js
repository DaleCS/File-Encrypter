/* ----- Client-side validation ----- */

// Validates the login user input
validateLoginInputThenSubmit = () => {
  let form = document.getElementById("loginForm");
  let errors = validateEmail(form.emailLogin.value);
  errors += validatePassword(form.passwordLogin.value);
  if (errors.length > 0) {
    let pErrorElement = document.getElementById("loginErrors");
    pErrorElement.textContent = errors;
    pErrorElement.classList.add("fadeIn");
  } else {
    form.submit();
  }
};

// Validates the sign up user input
validateSignupInput = () => {
  let form = document.getElementById("signupForm");
  let errors = validateEmail(form.emailSignup.value);
  errors += validateUsername(form.usernameSignup.value);
  errors += validatePassword(form.passwordSignup.value);
  if (errors.length > 0) {
    let pErrorElement = document.getElementById("signupErrors");
    pErrorElement.textContent = errors;
    pErrorElement.classList.add("fadeIn");
  } else {
    form.submit();
  }
};

// Validates the username that the user has submitted
validateUsername = field => {
  let trimmedField = field.trim();
  if (field.length > 0 && trimmedField != "") {
    if (field.length >= 4) {
      if (/[^\w\-]/.test(field) == false) {
        return "";
      } else {
        return "*Only alphanumeric characters, '-', and '_' are allowed in usernames\n";
      }
    } else {
      return "*Usernames must be at least 4 characters\n";
    }
  } else {
    return "*Username field is empty\n";
  }
};

// Validates the email that the user has submitted
validateEmail = field => {
  let trimmedField = field.trim();
  if (field.length > 0 && trimmedField != "") {
    if (
      /^[^\.](?!.*\.\.)[\w\.\-\+_]*[^\.@]@[^\.\-\_][\w\.\-\+_]+\.(?!.*web)[\w\.\-\+_\[\]]{2,}$/.test(
        field
      )
    ) {
      return "";
    } else {
      return "*Invalid email entered\n";
    }
  } else {
    return "*Email field is empty\n";
  }
};

// Validates the password that the user has submitted
validatePassword = field => {
  if (field.length > 0) {
    if (field.length >= 6) {
      if (/[a-z]/.test(field) && /[A-Z]/.test(field) && /[0-9]/.test(field)) {
        return "";
      } else {
        return "*Passwords must have at least one uppercase letter, one lowercase letter, and one numerical character\n";
      }
    } else {
      return "*Passwords must be at least 6 characters\n";
    }
  } else {
    return "*Password field is empty\n";
  }
};

// Validates the post that the user has submitted
validatePost = () => {
  let form = document.getElementById("postForm");

  let errors = "";

  if (validateActivity() == false || validateAlgorithm() == false) {
    errors += "*An error has occured\n";
  }

  errors += validateTitle();
  errors += validateText();

  if (errors.length > 0) {
    let pErrorElement = document.getElementById("postErrors");
    pErrorElement.textContent = errors;
    pErrorElement.classList.add("fadeIn");
  } else {
    // TODO: Do encryption here
    form.submit();
  }
};

// Check if the activity select values are from the expected ones
validateActivity = () => {
  try {
    let activitySelect = document.getElementById("activitySelect");
    let activity = activitySelect.options[activitySelect.selectedIndex].value;

    switch (activity) {
      case "encrypt":
      case "decrypt":
        return true;
        break;
      default:
        return false;
    }
  } catch (err) {
    console.log("catch activity");
    return false;
  }
};

// Check if the algorithm select values are from the expected ones
validateAlgorithm = () => {
  try {
    let algorithmSelect = document.getElementById("algorithmSelect");
    let algorithm =
      algorithmSelect.options[algorithmSelect.selectedIndex].value;

    switch (algorithm) {
      case "simple_substitution":
      case "double_transposition":
      case "rc4":
        return true;
        break;
      default:
        return false;
    }
  } catch (err) {
    console.log("catch algorithm");
    return false;
  }
};

// Validate post title input
validateTitle = () => {
  let title = document.getElementById("titlePost").value;
  if (title.length > 0 && title.trim() != "") {
    return "";
  } else {
    return "*Please title your post\n";
  }
};

// Checks if the user has submitted either a file upload or wrote on textarea
validateText = () => {
  let textareaContent = document.getElementById("textareaPost");
  let fileUpload = document.getElementById("fileUpload");

  let textareaDisabled = false;
  let fileUploadDisabled = false;

  for (let i = 0; i < textareaContent.attributes.length; i++) {
    if (textareaContent.attributes[i].name == "disabled") {
      textareaDisabled = true;
    }
  }
  for (let i = 0; i < fileUpload.attributes.length; i++) {
    if (fileUpload.attributes[i].name == "disabled") {
      fileUploadDisabled = true;
    }
  }

  if (textareaDisabled ^ fileUploadDisabled) {
    return "";
  } else {
    return "*Please either upload a .txt file or write one up using the textarea\n";
  }
};

// Prompts user that the credentials they have submitted is incorrect
incorrectEmailPassword = () => {
  let pErrorElement = document.getElementById("loginErrors");
  pErrorElement.textContent = "*Incorrect Email and/or Password\n";
  pErrorElement.classList.add("fadeIn");
};

// Prompts the user that the email that they've registered is already in use
emailAlreadyInUse = () => {
  let pErrorElement = document.getElementById("signupErrors");
  pErrorElement.textContent = "*Email is already taken\n";
  pErrorElement.classList.add("fadeIn");
};

// Deactivates text area when the user chooses to submit a file
deactivateTextarea = () => {
  let textarea = document.getElementById("textareaPost");
  textarea.classList.remove("dark-background-color");
  textarea.classList.add("main-background-color");
  textarea.setAttribute("disabled", true);
  textarea.setAttribute("placeholder", "You've chosen to upload a .txt file");
};

// Deactivates file upload when the user chooses to write on text area
deactivateFileUpload = () => {
  let textarea = document.getElementById("textareaPost").value;
  let fileUpload = document.getElementById("fileUpload");
  if (textarea.length > 0) {
    fileUpload.setAttribute("disabled", true);
  } else {
    fileUpload.removeAttribute("disabled");
  }
};
