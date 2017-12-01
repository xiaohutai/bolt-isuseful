# Is Useful Extension

This is a simple extension that adds the feature to add a "Is this page useful? Yes No"
to your pages. It has two modes:

- `link`: If a user clicks 'No', it will go to that page.
- `boltforms`: If a user clicks 'No', it will expand a form (requires `bolt/boltforms`).

For `link` mode, you can get the `url` parameter from the query string.

For `boltforms` mode, you need to add a form named `feedback` and set `ajax` to
`true`. An example form as follows:

```
feedback:
    submission:
        ajax: true
    notification:
        enabled: true
        # debug: true
        # debug_address:
        # debug_smtp: true
        subject: Feedback
        from_name: no-reply@example.com
        from_email: no-reply@example.com
        to_name: Example
        to_email: info@example.com
    feedback:
        success: Success
        error: Error
    fields:
        message:
            type: textarea
            options:
                required: false
                label: Feedback
                attr:
                    placeholder: How should we improve this page?
                    class: message
        url:
            type: hidden
            options:
                label: URL
                required: false
                attr:
                    class: hidden
        type:
            type: hidden
            options:
                label: Type
                required: false
                attr:
                    class: hidden
        id:
            type: hidden
            options:
                label: ID
                required: false
                attr:
                    class: hidden
        submit:
            type: submit
            options:
                label: Send
                attr:
                    class: button primary
```

In your template add:

```
{{ include('@is_useful/_is_useful.twig') }}
```

Or copy the twig file to your theme, customize it and include that file.


## Ideas and Thoughts

- Track individual "Yes" and "No" clicks in the database.
  - Could be nice for something like "80% of visitors found this page useful".
- Allow BoltForms formnames other than `'feedback'`.
  - Currently there's a hard-coded formname (in JavaScript).
- Handle BoltForms errors.
  - Currently there is no error handling once the AJAX request is made.
- Handle ReCaptcha
  - Currently it does not work if ReCaptcha is enabled for that form.

<!--
Inspired from GOV.UK, e.g. https://www.gov.uk/service-manual/measuring-success/measuring-user-satisfaction
-->
