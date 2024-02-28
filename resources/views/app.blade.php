<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>




</body>

<script>
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        console.log(value);
        const parts = value.split(`; ${name}=`);
        console.log(parts);
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
    }

    function request(url, options) {
        // get cookie
        const csrfToken = getCookie('XSRF-TOKEN');
        console.log(csrfToken);
        return fetch(url, {
            headers: {
                'content-type': 'application/json',
                'accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(csrfToken),
            },
            credentials: 'include',
            ...options,
        })
    }

    function logout() {
        return request('/logout', {
            method: 'POST'
        });
    }

    function login() {
        return request('/login', {
            method: "POST",
            body: JSON.stringify({
                email: 'todimu@example.net',
                'password': 'password'
            })
        })
    }

    fetch('/sanctum/csrf-cookie', {
            headers: {
                'content-type': 'application/json',
                'accept': 'application/json'
            },
            credentials: 'include'
        }).then(() => logout())
        .then(() => {
            return login();
        })
        .then(() => request('/api/getStatus'))
</script>

</html>