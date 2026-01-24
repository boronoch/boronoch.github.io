// Define the tasks with their times (24-hour format)
const tasks = [
    { task: "Before 5:05: sleep, stretch, or make coffee", time: "05:05" },
    { task: "5:05: Alarm", time: "05:05" },
    { task: "5:10: Flex room, read a reading, say short prayer to the Holy Spirit", time: "05:10" },
    { task: "5:20: Duolingo, stretch", time: "05:20" },
    { task: "Crunches and sit-ups", time: "05:20" },  // This overlaps with Duolingo/stretch
    { task: "Make coffee", time: "05:20" },  // This also overlaps
    { task: "5:30: Shower", time: "05:30" },
    { task: "5:40: Shave, brush teeth", time: "05:40" },
    { task: "5:55: Make oatmeal", time: "05:55" },
    { task: "6:05: Eat", time: "06:05" },
    { task: "6:15: Pack lunch(es)", time: "06:15" },
    { task: "6:20: Pack bag", time: "06:20" },
    { task: "6:30: Leave", time: "06:30" }
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
