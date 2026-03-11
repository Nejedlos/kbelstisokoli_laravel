# Change History and Audit Logs

Did someone change the color in the settings or delete a player and you don't know who? Audit logs are used to track all important actions in the system.

## Where to Find the Section
In the administration, in the left menu under **System > Change History**.

## What We Log
The system automatically records:
- **Event**: Created, Updated, Deleted.
- **Model**: What type of data it was (e.g., User, Post, Team).
- **Who**: The name and e-mail of the user who performed the action.
- **When**: The exact time and date.

## Searching and Filtering
If you are looking for a specific event:
1. Use the table search by user name or e-mail.
2. Filter by **Event** (e.g., if you only want to see deleted records).
3. In the record detail (eye icon), you will see exactly what changed – the original value (Old) vs. the new value (New).

## Important Notice
- **Recovering Deleted Data**: The audit log serves to inform about the deletion, but it does not contain a button for automatic recovery of the deleted record. Data recovery requires technical intervention by a database administrator.
- **Data Retention**: Old records in the log may be automatically deleted over time to keep the system fast.
