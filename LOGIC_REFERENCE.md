# UASG Task Status Logic Reference

## Database Values (task_tbl.task_status)
- `active` — Task is live/ongoing
- `closed` — Task completed and closed by admin
- `cancelled` — Task cancelled by admin

## Kanban Board Classification (Admin Side)
The kanban status is **derived** from the DB status + submission data:

| Kanban Status      | DB task_status | Submission Condition                                      |
|--------------------|----------------|-----------------------------------------------------------|
| Pending            | `active`       | No submissions in `task_submission_tbl`                   |
| Awaiting Review    | `active`       | Has submissions (`submission_count > 0`) but none approved |
| Approved           | `active`       | Has at least one approved submission (`approved_count > 0`)|
| Closed             | `closed`       | —                                                         |
| Cancelled          | `cancelled`    | —                                                         |

## Key Tables
- `task_tbl` — Main task table. `assigned_to` = user_id of the member assigned.
- `task_submission_tbl` — Submissions by members. Links via `task_id`. Has `check_status` field for approval state.
- `task_category_tbl` — Task categories. Links via `task_category_id`.

## Member Perspective
- Members see tasks where `assigned_to = their user_id`
- "Active" tasks for members = `task_status = 'active'` (covers Pending, Awaiting Review, Approved from admin kanban)
- `getMemberActiveTasks()` additionally filters: deadline >= today AND no non-rejected submission exists

## Admin/Subadmin Global Search
- Searches all tasks by title (no status filter needed — admin sees everything)

## Member Global Search
- Filters by `assigned_to = user_id` AND `task_status = 'active'`
- This returns tasks in Pending, Awaiting Review, or Approved kanban states
- Excludes closed/cancelled tasks
