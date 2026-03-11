# Scheduled Tasks (Cron)

Scheduled tasks are the "invisible workers" of the KS system. In the background, they ensure that data is always current, that emails are sent, and that the system isn't cluttered with old files.

### Overview of Tasks
In the **System > Scheduled Tasks** section, you can see a list of all processes:
- **Payment Synchronization:** Downloads transactions from the bank (typically every hour).
- **Sports Data Synchronization:** Downloads results and standings from ČBF.
- **Cache Clearing:** Removes temporary files to speed up the website.
- **Notification Sending:** Processes the queue for emails and alerts.

### Status and Monitoring
For each task, you can see its latest status:
- **Success (Green):** The task completed correctly.
- **Running (Blue):** The task is currently in progress.
- **Failed (Red):** An error occurred. In this case, we recommend checking the **Task Logs**, where the cause of the failure is described (e.g., ČBF API outage).

### Manual Start (Run Now)
Sometimes you don't want to wait for the automatic interval (e.g., you just uploaded a new bank statement or a match has finished).
1. Find the correct task in the list.
2. Use the **Run Now** action.
3. The task will be placed at the head of the queue and executed immediately.

### Logs and History
The **Task Logs** section preserves a history of all runs. If the system shows data discrepancies, this is the first place an administrator should look. Logs also contain technical details regarding the number of imported records.

### Warning for Administrators
- **Do Not Delete Tasks:** If you delete a task, that part of the system will stop updating. If you only want to stop it temporarily, use the "Active" field.
- **Cycling:** If you manually start a task that is already running, the system will queue it. Do not run the same task multiple times in a short interval.
