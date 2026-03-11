# Scenario: Termination of Membership (Exit Process)

When a player or coach decides to leave the club, it's important to correctly close their involvement in the system so that they don't receive payment reminders and don't have access to internal data.

## Step 1: Finance Check
1. Open the user's profile and look at the **Financial Settlement**.
2. If the player has a debt, agree on its settlement.
3. If they have an overpayment, the system doesn't automatically return it in the future; it must be resolved accounting-wise.

## Step 2: Turning Off Payments
1. In the user's profile on the **Club** tab (in the season configuration), set the **Billing end month**.
2. This ensures that from the next month, the system will no longer generate new charges (contributions).

## Step 3: Account Deactivation
1. In the user list, switch the **is_active** flag to **False** for that person.
2. This immediately denies the user access to both the administration and the member section.
3. **Important**: Don't delete the user entirely if you want to preserve their historical statistics and payment records.

## Step 4: Removal from Rosters
1. Remove the user from all active teams so they don't appear in attendance and nomination lists.

## Step 5: Equipment Return
1. If the user has a borrowed jersey or other equipment, verify its return.

## Tips
- **Archiving**: Deactivated users remain in the database. You can reactivate them at any time if they return to the club.
