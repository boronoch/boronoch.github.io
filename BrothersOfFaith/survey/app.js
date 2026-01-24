// app.js

// Admin Page Functionality
async function fetchTopics() {
    try {
        const response = await fetch('/api/topics'); // Endpoint to fetch topics from the database
        const topics = await response.json();
        const topicList = document.getElementById('topic-list');
        topicList.innerHTML = '';

        topics.forEach(topic => {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = `topic-${topic.id}`;
            checkbox.value = topic.id;

            const label = document.createElement('label');
            label.htmlFor = `topic-${topic.id}`;
            label.textContent = topic.title;

            const container = document.createElement('div');
            container.appendChild(checkbox);
            container.appendChild(label);
            topicList.appendChild(container);
        });
    } catch (error) {
        console.error('Error fetching topics:', error);
    }
}

async function saveSurveyTopics() {
    const selectedTopics = Array.from(document.querySelectorAll('#topic-list input:checked')).map(checkbox => checkbox.value);

    try {
        const response = await fetch('/api/save-survey-topics', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ topicIds: selectedTopics })
        });

        if (response.ok) {
            alert('Survey topics saved successfully!');
        } else {
            alert('Failed to save survey topics.');
        }
    } catch (error) {
        console.error('Error saving survey topics:', error);
    }
}

// Survey Form Functionality
async function loadSurveyForm() {
    try {
        const response = await fetch('/api/survey-topics'); // Endpoint to fetch selected topics
        const topics = await response.json();
        const surveyTopics = document.getElementById('survey-topics');
        surveyTopics.innerHTML = '';

        topics.forEach(topic => {
            const container = document.createElement('div');

            const title = document.createElement('h4');
            title.textContent = topic.title;
            container.appendChild(title);

            const description = document.createElement('p');
            description.textContent = topic.description;
            container.appendChild(description);

            const rankLabel = document.createElement('label');
            rankLabel.textContent = 'Rank (1-6):';
            const rankInput = document.createElement('input');
            rankInput.type = 'number';
            rankInput.min = '1';
            rankInput.max = '6';
            rankInput.name = `rank-${topic.id}`;
            container.appendChild(rankLabel);
            container.appendChild(rankInput);

            const leadCheckbox = document.createElement('input');
            leadCheckbox.type = 'checkbox';
            leadCheckbox.name = `lead-${topic.id}`;
            const leadLabel = document.createElement('label');
            leadLabel.textContent = 'Willing to lead';
            container.appendChild(leadCheckbox);
            container.appendChild(leadLabel);

            const commentLabel = document.createElement('label');
            commentLabel.textContent = 'Comments:';
            const commentInput = document.createElement('textarea');
            commentInput.name = `comment-${topic.id}`;
            container.appendChild(commentLabel);
            container.appendChild(commentInput);

            surveyTopics.appendChild(container);
        });
    } catch (error) {
        console.error('Error loading survey form:', error);
    }
}

async function submitSurvey(event) {
    event.preventDefault();

    const formData = new FormData(document.getElementById('survey-form'));
    const surveyData = {
        name: formData.get('participant-name'),
        topics: []
    };

    document.querySelectorAll('#survey-topics > div').forEach(container => {
        const topicId = container.querySelector('h4').textContent;
        surveyData.topics.push({
            id: topicId,
            rank: formData.get(`rank-${topicId}`),
            willingToLead: formData.has(`lead-${topicId}`),
            comment: formData.get(`comment-${topicId}`)
        });
    });

    surveyData.additionalComments = formData.get('additional-comments');

    try {
        const response = await fetch('/api/submit-survey', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(surveyData)
        });

        if (response.ok) {
            alert('Survey submitted successfully!');
        } else {
            alert('Failed to submit survey.');
        }
    } catch (error) {
        console.error('Error submitting survey:', error);
    }
}

// Results Page Functionality
async function loadSurveyResults() {
    try {
        const response = await fetch('/api/survey-results');
        const results = await response.json();
        const resultsContainer = document.getElementById('results-container');
        resultsContainer.innerHTML = '';

        results.forEach(result => {
            const container = document.createElement('div');

            const name = document.createElement('h4');
            name.textContent = `Name: ${result.name}`;
            container.appendChild(name);

            result.topics.forEach(topic => {
                const topicContainer = document.createElement('div');
                const title = document.createElement('h5');
                title.textContent = `Topic: ${topic.title}`;
                topicContainer.appendChild(title);

                const rank = document.createElement('p');
                rank.textContent = `Rank: ${topic.rank}`;
                topicContainer.appendChild(rank);

                const lead = document.createElement('p');
                lead.textContent = `Willing to Lead: ${topic.willingToLead ? 'Yes' : 'No'}`;
                topicContainer.appendChild(lead);

                const comment = document.createElement('p');
                comment.textContent = `Comment: ${topic.comment}`;
                topicContainer.appendChild(comment);

                container.appendChild(topicContainer);
            });

            const additionalComments = document.createElement('p');
            additionalComments.textContent = `Additional Comments: ${result.additionalComments}`;
            container.appendChild(additionalComments);

            resultsContainer.appendChild(container);
        });
    } catch (error) {
        console.error('Error loading survey results:', error);
    }
}

// Initialize App
fetchTopics();
loadSurveyForm();
loadSurveyResults();
document.getElementById('survey-form').addEventListener('submit', submitSurvey);
