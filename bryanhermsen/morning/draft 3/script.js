// Schedule items with tasks and times in 24-hour format
const tasks = [
  { time: '04:59', description: 'Before 5:05 - Sleep, stretch, or make coffee' },
  { time: '05:05', description: 'Alarm' },
  { time: '05:10', description: 'Flex room - Read a reading, say a short prayer to the Holy Spirit' },
  { time: '05:20', description: 'Duolingo, stretch' },
  { time: '05:20', description: 'Crunches and sit-ups' },
  { time: '05:20', description: 'Make coffee' },
  { time: '05:30', description: 'Shower' },
  { time: '05:40', description: 'Shave, brush teeth' },
  { time: '05:55', description: 'Make oatmeal' },
  { time: '06:05', description: 'Eat' },
  { time: '06:15', description: 'Pack lunch(es)' },
  { time: '06:20', description: 'Pack bag' },
  { time: '06:30', description: 'Leave for the office' }
];

const taskList = document.getElementById('taskList');

// Helper function to get Central Time (CT)
function getCentralTime() {
  const now = new Date();
  return new Date(now.toLocaleString('en-US', { timeZone: 'America/Chicago' }));
}

// Create task list items
tasks.forEach((task, index) => {
  const taskDiv = document.createElement('div');
  taskDiv.className = 'task';
  
  const checkbox = document.createElement('input');
  checkbox.type = 'checkbox';
  checkbox.id = `task-${index}`;
  checkbox.addEventListener('change', () => updateTaskStatus(taskSpan, checkbox, task.time));

  const label = document.createElement('label');
  label.htmlFor = `task-${index}`;

  const taskSpan = document.createElement('span');
  taskSpan.textContent = task.time ? `${task.time} - ${task.description}` : task.description;
  taskSpan.classList.add('pending');
  
  label.appendChild(taskSpan);
  taskDiv.appendChild(checkbox);
  taskDiv.appendChild(label);
  taskList.appendChild(taskDiv);
});

// Update the task style based on time and checkbox state
function updateTaskStatus(taskSpan, checkbox, time) {
  if (checkbox.checked) {
    taskSpan.className = 'completed';
  } else {
    const currentTime = getCentralTime();
    const taskTime = new Date();
    if (time) {
      const [hours, minutes] = time.split(':');
      taskTime.setHours(parseInt(hours), parseInt(minutes), 0);
      taskSpan.className = taskTime <= currentTime ? 'active' : 'pending';
    } else {
      taskSpan.className = 'pending';
    }
  }
}

// Initial update and periodic check for time-sensitive tasks
function checkTaskTimes() {
  const currentTime = getCentralTime();

  tasks.forEach((task, index) => {
    const taskSpan = document.querySelector(`#task-${index} ~ label span`);
    const checkbox = document.getElementById(`task-${index}`);
    const taskTime = new Date();

    if (task.time) {
      const [hours, minutes] = task.time.split(':');
      taskTime.setHours(parseInt(hours), parseInt(minutes), 0);

      if (!checkbox.checked) {
        taskSpan.className = taskTime <= currentTime ? 'active' : 'pending';
      }
    }
  });
}

// Update task statuses every minute
setInterval(checkTaskTimes, 60000);
checkTaskTimes(); // Initial check on load
