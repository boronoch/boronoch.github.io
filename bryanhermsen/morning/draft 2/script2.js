// Define the tasks with their times (24-hour format)
const tasks = [
    { task: "Wake up", time: "06:00" },
    { task: "Brush teeth", time: "06:15" },
    { task: "Get dressed", time: "06:30" },
    { task: "Eat breakfast", time: "06:45" },
    { task: "Pack bag", time: "07:00" },
    { task: "Leave house", time: "07:15" }
];

// Function to get the current time in "HH:mm" format (Central Time)
function getCurrentTime() {
    const now = new Date();
    const options = { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'America/Chicago' };
    return now.toLocaleTimeString('en-US', options);
}

// Function to update the task list based on current time
function updateTaskList() {
    const taskList = document.getElementById('task-list');
    const currentTime = getCurrentTime();
    
    // Clear the existing task list
    taskList.innerHTML = '';

    tasks.forEach(task => {
        const li = document.createElement('li');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        
        // Check if the task is completed
        if (task.completed) {
            li.classList.add('completed');
            checkbox.checked = true;
        } else {
            checkbox.checked = false;
        }

        // If the task time is in the future, apply the 'disabled' class
        if (currentTime < task.time) {
            li.classList.add('disabled');
        } else {
            li.classList.add('in-progress');
        }

        // When checkbox is clicked, mark as completed
        checkbox.addEventListener('change', () => {
            task.completed = checkbox.checked;
            if (checkbox.checked) {
                li.classList.add('completed');
            } else {
                li.classList.remove('completed');
            }
        });

        // Add the time and label
        const label = document.createElement('label');
        label.textContent = `${task.task} - ${task.time}`;
        li.appendChild(checkbox);
        li.appendChild(label);
        taskList.appendChild(li);
    });
}

// Initial load
updateTaskList();

// Update the task list every minute
setInterval(updateTaskList, 60000);
