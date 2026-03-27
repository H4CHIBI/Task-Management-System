Task Management System
A streamlined Project Management tool designed to help users organize their workflow, track progress, and maintain a clear hierarchy between high-level goals and day-to-day tasks.

## Project Overview
This system is built to bridge the gap between "what needs to happen" and "who is doing it." By leveraging a structured relational hierarchy, the application ensures that every task has a home and every project has an owner.

The core logic revolves around a User-Centric Project Model, where individual accounts manage distinct project silos, each containing its own set of actionable tasks.

## Key Features
📂 Project Organization
Ownership Tracking: Every project is tied to a specific user, ensuring data privacy and clear accountability.

Detailed Documentation: Support for long-form project descriptions to house mission statements or requirements.

✅ Task Lifecycle Management
Granular Control: Tasks are nested within projects to keep the workspace organized and context-specific.

Dynamic Statuses: Track work through custom stages (e.g., To Do, In Progress, Review).

Priority Weighting: Built-in priority levels to help users focus on high-impact items first.

Completion Logic: A simple boolean "Completion" toggle for quick filtering of active vs. archived work.

## Technical Workflow
The system operates on a One-to-Many relational flow:

Authentication: Users enter the system via unique credentials and assigned roles.

Project Creation: An authenticated user initializes a project, becoming the "Owner."

Task Allocation: The owner populates the project with tasks. Each task is mapped back to the parent project ID to maintain integrity.

Execution: As work progresses, the status and is_completed flags are updated to reflect real-time progress.

## Future Roadmap
Role-Based Access Control (RBAC): Expanding the role attribute to limit project creation or deletion to specific user types.

Multi-User Collaboration: Moving beyond single ownership to allow teams to share a single project.

Deadline Notifications: Adding temporal tracking (Due Dates) to the task level.