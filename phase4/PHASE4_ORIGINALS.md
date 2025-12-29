## 🌐 Phase 4: Frontend Website & SaaS Platform

Now we build the actual SaaS website where users interact with your AI-powered development platform.

### Prompt 16: Create the React Frontend Application
Create a modern React frontend for the Cursoft SaaS platform. Use Vite + TypeScript + Tailwind CSS. Create the following structure:

**File:** `frontend/package.json` (initial setup)

**Requirements:**
1. Dependencies:
   - React 18 + TypeScript
   - Vite for build tool
   - Tailwind CSS for styling
   - React Router for navigation
   - Axios for API calls
   - React Query for state management
   - Socket.io-client for real-time updates
   - React Hook Form for forms
   - Zod for validation
   - Lucide React for icons

2. Scripts:
```json
"scripts": {
  "dev": "vite",
  "build": "tsc && vite build",
  "lint": "eslint src --ext ts,tsx --report-unused-disable-directives --max-warnings 0",
  "preview": "vite preview"
}
```

3. Vite Configuration: `frontend/vite.config.ts`
4. Tailwind Configuration: `frontend/tailwind.config.js`
5. TypeScript Configuration: `frontend/tsconfig.json`

**First, create the package.json and basic config files.**

### Prompt 17: Create the Main Layout and Routing
Create the main layout component and routing structure. Create these files:

**File 1:** `frontend/src/App.tsx`

**Requirements:**
1. Router Setup:
```tsx
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { Toaster } from 'react-hot-toast'

// Pages
import Dashboard from './pages/Dashboard'
import NewProject from './pages/NewProject'
import ProjectDetail from './pages/ProjectDetail'
import LLMConfig from './pages/LLMConfig'
import Billing from './pages/Billing'
import Login from './pages/Login'
import Signup from './pages/Signup'

// Layout
import MainLayout from './layouts/MainLayout'
```

2. Query Client Setup:
```tsx
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5, // 5 minutes
      retry: 1
    }
  }
})
```

3. Protected Route Wrapper:
```tsx
const ProtectedRoute = ({ children }: { children: React.ReactNode }) => {
  const { user, loading } = useAuth()

  if (loading) return <LoadingSpinner />
  if (!user) return <Navigate to="/login" />

  return <>{children}</>
}
```

4. Route Structure:
```tsx
<Routes>
  <Route path="/login" element={<Login />} />
  <Route path="/signup" element={<Signup />} />

  <Route path="/" element={
    <ProtectedRoute>
      <MainLayout />
    </ProtectedRoute>
  }>
    <Route index element={<Dashboard />} />
    <Route path="new" element={<NewProject />} />
    <Route path="project/:id" element={<ProjectDetail />} />
    <Route path="llm-config" element={<LLMConfig />} />
    <Route path="billing" element={<Billing />} />
  </Route>

  <Route path="*" element={<NotFound />} />
</Routes>
```

**File 2:** `frontend/src/layouts/MainLayout.tsx`

**Requirements:**
1. Sidebar Navigation:
   - Logo and platform name
   - Navigation links (Dashboard, New Project, LLM Config, Billing)
   - User profile dropdown
   - Dark/light mode toggle

2. Main Content Area:
   - Header with breadcrumbs
   - Outlet for page content
   - Notification center

3. Responsive Design:
   - Collapsible sidebar on mobile
   - Mobile-friendly navigation

**Use Tailwind CSS for styling, make it look modern and professional.**

### Prompt 18: Create the New Project Page (Core Feature)
Create the main page where users input prompts and configure projects.

**File:** `frontend/src/pages/NewProject.tsx`

**Requirements:**
1. State Management:
```ts
interface ProjectFormData {
  prompt: string
  projectName: string
  selectedLLMs: string[]
  permissionLevel: 'restricted' | 'standard' | 'elevated'
  notificationEmail: string
  budgetLimit?: number
}
```

2. UI Components:
- Prompt Input:
  * Large textarea with placeholder examples
  * Character counter (min 20, max 2000 chars)
  * Prompt suggestions: "Try: 'Build a todo app with React and Firebase'"
- LLM Selector Panel:
  * Grid of LLM provider cards (OpenAI, Anthropic, Google, DeepSeek, Ollama)
  * Each card shows: Name, cost indicator, best for, models available
  * Checkbox to select/deselect
  * "Select All" / "Deselect All" buttons
  * Real-time cost estimate as LLMs are selected
- Configuration Options:
  * Permission level dropdown with explanations
  * Project name input (auto-generated from prompt but editable)
  * Notification email input
  * Budget limit slider ($1-$100)
- Preview Panel:
  * Shows parsed project plan from backend
  * Estimated timeline
  * Technology stack preview
  * Cost estimate breakdown

3. API Integration:
```ts
const createProject = async (data: ProjectFormData) => {
  const response = await axios.post('/api/v1/projects', data)
  return response.data.pipeline_id
}
```

4. Real-time Features:
- Debounced prompt analysis (after 1.5s of no typing)
- Auto-fetch project plan preview
- Cost calculation as LLMs are selected
- Form validation with Zod

5. Step-by-Step Wizard (Optional):
Step 1: Describe your project
Step 2: Choose AI providers
Step 3: Configure settings
Step 4: Review & create

6. Example Prompts Gallery:
- Show 3-5 example prompts users can click to try
- Categorized: Web Apps, APIs, Tools, Games, etc.

7. Submit Button & Flow:
- On submit, show loading state
- Redirect to project detail page with pipeline_id
- Show success/error toast notifications

**Design:** Make this page visually impressive - it's your main conversion page.

### Prompt 19: Create the Project Detail Page with Real-time Updates
Create the project detail page that shows real-time development progress.

**File:** `frontend/src/pages/ProjectDetail.tsx`

**Requirements:**
1. WebSocket Connection:
```ts
const connectWebSocket = (pipelineId: string) => {
  const socket = io(`ws://localhost:8000/ws/projects/${pipelineId}/progress`, {
    transports: ['websocket']
  })

  socket.on('progress_update', (data) => {
    setProgress(data)
  })

  return () => socket.disconnect()
}
```

2. UI Components:
A. Progress Overview Card:
- Project name and status badge
- Progress bar (0-100%)
- Time elapsed / estimated time remaining
- Current stage name
- Start time / Last update

B. Pipeline Stage Visualization:
```ts
const STAGES = [
  { id: 'planning', name: 'Planning', icon: Brain },
  { id: 'environment', name: 'Environment Setup', icon: Server },
  { id: 'development', name: 'Development', icon: Code },
  { id: 'testing', name: 'Testing', icon: TestTube },
  { id: 'integration', name: 'Integration', icon: GitMerge },
  { id: 'deployment', name: 'Deployment Prep', icon: Package },
  { id: 'final', name: 'Finalization', icon: CheckCircle }
]
```
- Visual pipeline with current stage highlighted
- Stage status indicators (pending, running, completed, failed)
- Click any stage to see detailed logs

C. Real-time Log Viewer:
- Auto-scrolling terminal-like output
- Filter logs by type (info, warning, error, success)
- Search within logs
- Copy logs button
- Log levels with color coding

D. File Explorer Panel:
- Tree view of generated files
- Click files to preview content
- File icons by type
- Download individual files

E. Code Preview Panel:
- Syntax-highlighted code viewer
- Line numbers
- Copy code button
- Switch between files

F. Action Buttons:
- Pause/Resume development
- Request human intervention
- Download project (when complete)
- Clone project to start new version
- Share project (generate shareable link)

3. Status-specific Views:
- Running: Show progress, logs, estimated completion
- Completed: Show success message, download buttons, project summary
- Failed: Show error details, retry options, support contact
- Paused: Show pause reason, resume button

4. Metrics Dashboard:
- Token usage per LLM
- Cost breakdown
- Development time
- Lines of code generated
- Tests passed/failed

5. Interactive Features:
- Click any error in logs to see suggested fix
- "Speed up" button to add more LLM capacity (paid feature)
- "Simplify" button to reduce scope if taking too long
- "Add feature" button to modify requirements mid-development

**Make this page feel like watching magic happen - real-time AI development.**

### Prompt 20: Create the LLM Configuration Page
Create the page where users configure their LLM providers and API keys.

**File:** `frontend/src/pages/LLMConfig.tsx`

**Requirements:**
1. Provider Configuration Cards:
For each LLM provider (OpenAI, Anthropic, Google, DeepSeek, Ollama):

Card Structure:
- Provider logo/icon
- Enable/Disable toggle
- API key input (masked, with show/hide)
- Model selection dropdown
- Base URL (for custom endpoints)
- Temperature slider (0.0 - 1.0)
- Max tokens input
- Cost per 1K tokens display
- "Test Connection" button

2. API Key Management:
- Secure storage (encrypted in backend)
- Validation on input
- Test connection before saving
- Option to delete/rotate keys
- Usage statistics for each key

3. Provider Comparison Table:
| Feature | OpenAI | Anthropic | Google | DeepSeek | Ollama |
|---------|--------|-----------|--------|----------|--------|
| Best For | ... | ... | ... | ... | ... |
| Cost | ... | ... | ... | ... | ... |
| Speed | ... | ... | ... | ... | ... |
| Code Quality | ... | ... | ... | ... | ... |
| Context Window | ... | ... | ... | ... | ... |

4. Recommendation Engine:
```ts
const getRecommendedProviders = (projectType: string): string[] => {
  const recommendations = {
    'web-app': ['openai', 'deepseek'],
    'api': ['anthropic', 'deepseek'],
    'data-science': ['google', 'openai'],
    'mobile': ['openai', 'anthropic'],
    'game': ['openai', 'google']
  }
  return recommendations[projectType] || ['openai']
}
```

5. Cost Calculator:
- Estimate monthly cost based on usage
- Compare provider costs for same task
- Set monthly spending limits
- Alert when approaching limit

6. Advanced Settings:
- Custom prompts per provider
- Fallback order configuration
- Retry settings
- Timeout configuration
- Response caching options

7. Usage Analytics:
- Tokens used per provider (chart)
- Cost per project
- Success/failure rates
- Average response time

8. Bulk Operations:
- Enable/disable all providers
- Copy settings between providers
- Reset to defaults
- Export/import configuration

Security Note: Never expose full API keys in UI, only last 4 characters.

### Prompt 21: Create Authentication Pages
Create the login and signup pages.

**File 1:** `frontend/src/pages/Login.tsx`

Requirements:
1. Login Form:
- Email input
- Password input
- "Remember me" checkbox
- Forgot password link
- Submit button
- Social login buttons (Google, GitHub)

2. Features:
- Form validation with Zod
- Loading state during submission
- Error message display
- Redirect after successful login
- "Demo mode" button to try without account

3. Security:
- Rate limiting feedback
- CAPTCHA for multiple failed attempts
- Password strength meter (for signup)
- 2FA setup flow (advanced)

**File 2:** `frontend/src/pages/Signup.tsx`

Requirements:
1. Signup Form:
- Name input
- Email input (with verification)
- Password input (with strength meter)
- Confirm password
- Terms of service checkbox
- Newsletter opt-in

2. Plan Selection:
- Free tier: 3 projects/month, basic LLMs
- Pro tier: Unlimited projects, all LLMs, priority
- Enterprise: Custom limits, dedicated support

3. Email Verification Flow:
- Send verification email
- Verification code input
- Resend verification button

4. Onboarding Wizard:
- Step 1: Account details
- Step 2: Choose plan
- Step 3: Payment (if paid plan)
- Step 4: Verify email
- Step 5: Welcome tutorial

**File 3:** `frontend/src/components/AuthProvider.tsx`
- React Context for authentication state
- Token management
- Auto-refresh tokens
- Protected route logic
- User profile management

### Prompt 22: Create the Dashboard Page
Create the main dashboard page.

**File:** `frontend/src/pages/Dashboard.tsx`

**Requirements:**
1. Dashboard
NOTE: Prompt 22 was cut off at "1. Dashboard" in the provided text.
