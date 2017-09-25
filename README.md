# Social Hub Extension

Helper functions for the Social Hub website.

# Is Useful Extension

This is a simple extension that adds the feature to add a  "Is this page useful? Yes No"
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

<!--
Inspired from GOV.UK, e.g. https://www.gov.uk/service-manual/measuring-success/measuring-user-satisfaction
-->
