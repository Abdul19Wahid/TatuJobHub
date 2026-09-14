<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/app/models/SeekerProfile.php';

class ResumePdfController extends Controller
{
    public function download(): void
    {
        $this->requireRole('seeker');

        $profiles = new SeekerProfile();
        $profile  = $profiles->getByUserId(Session::id());
        if (!$profile) $this->abort(404);

        $skills = $profiles->getSkills($profile['id']);
        $exp    = $profiles->getExperience($profile['id']);
        $edu    = $profiles->getEducation($profile['id']);

        // Build HTML for the PDF
        $html = $this->buildResumeHtml($profile, $skills, $exp, $edu);

        // Output as downloadable HTML file (print-ready)
        // For a true PDF: install wkhtmltopdf or use dompdf via Composer
        // For now we serve a print-ready HTML that the browser can Print → Save as PDF
        $filename = slug($profile['full_name']) . '-resume.html';

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $html;
        exit;
    }

    public function preview(): void
    {
        $this->requireRole('seeker');

        $profiles = new SeekerProfile();
        $profile  = $profiles->getByUserId(Session::id());
        if (!$profile) $this->abort(404);

        $skills = $profiles->getSkills($profile['id']);
        $exp    = $profiles->getExperience($profile['id']);
        $edu    = $profiles->getEducation($profile['id']);

        header('Content-Type: text/html; charset=utf-8');
        echo $this->buildResumeHtml($profile, $skills, $exp, $edu);
        exit;
    }

    private function buildResumeHtml(
        array $p,
        array $skills,
        array $exp,
        array $edu
    ): string {
        $name     = e($p['full_name']);
        $headline = e($p['headline'] ?? '');
        $email    = e($p['email'] ?? '');
        $phone    = e($p['phone'] ?? '');
        $location = trim(e($p['location_city'] ?? '') . ', ' . e($p['location_country'] ?? ''), ', ');
        $linkedin = e($p['linkedin_url'] ?? '');
        $github   = e($p['github_url'] ?? '');
        $bio      = nl2br(e($p['bio'] ?? ''));
        $years    = $p['years_experience'] ?? 0;

        // Skills HTML
        $skillsHtml = '';
        if (!empty($skills)) {
            $skillsHtml = '<div class="section"><div class="section-title">Skills</div><div class="skills">';
            foreach ($skills as $sk) {
                $skillsHtml .= '<span class="skill-tag">'
                    . e($sk['skill_name'])
                    . ' <em>(' . ucfirst($sk['proficiency']) . ')</em></span>';
            }
            $skillsHtml .= '</div></div>';
        }

        // Experience HTML
        $expHtml = '';
        if (!empty($exp)) {
            $expHtml = '<div class="section"><div class="section-title">Work Experience</div>';
            foreach ($exp as $e_) {
                $end = $e_['is_current'] ? 'Present' : date('M Y', strtotime($e_['end_date'] ?? 'now'));
                $expHtml .= '
                <div class="item">
                    <div class="item-header">
                        <span class="item-title">' . e($e_['job_title']) . '</span>
                        <span class="item-date">'
                            . date('M Y', strtotime($e_['start_date'])) . ' — ' . $end
                        . '</span>
                    </div>
                    <div class="item-sub">' . e($e_['company_name'])
                        . ($e_['location'] ? ' · ' . e($e_['location']) : '') . '</div>'
                    . ($e_['description'] ? '<p class="item-desc">' . nl2br(e($e_['description'])) . '</p>' : '')
                    . '</div>';
            }
            $expHtml .= '</div>';
        }

        // Education HTML
        $eduHtml = '';
        if (!empty($edu)) {
            $eduHtml = '<div class="section"><div class="section-title">Education</div>';
            foreach ($edu as $ed) {
                $end = $ed['is_current'] ? 'Present' : ($ed['end_year'] ?? '');
                $eduHtml .= '
                <div class="item">
                    <div class="item-header">
                        <span class="item-title">' . e($ed['degree']) . '</span>
                        <span class="item-date">' . $ed['start_year'] . ' — ' . $end . '</span>
                    </div>
                    <div class="item-sub">' . e($ed['institution'])
                        . ($ed['grade'] ? ' · Grade: ' . e($ed['grade']) : '') . '</div>'
                    . ($ed['field_of_study'] ? '<p class="item-desc">' . e($ed['field_of_study']) . '</p>' : '')
                    . '</div>';
            }
            $eduHtml .= '</div>';
        }

        // Contact links
        $contactHtml = '';
        if ($email)    $contactHtml .= '<span>✉ ' . $email . '</span>';
        if ($phone)    $contactHtml .= '<span>📞 ' . $phone . '</span>';
        if ($location) $contactHtml .= '<span>📍 ' . $location . '</span>';
        if ($linkedin) $contactHtml .= '<span>🔗 LinkedIn</span>';
        if ($github)   $contactHtml .= '<span>💻 GitHub</span>';

        // Pre-compute conditional blocks (heredoc can't use ternary operators in PHP 8.2)
        $headlineHtml  = $headline ? "<p class='headline'>{$headline}</p>" : '';
        $yearsHtml     = $years    ? "<p class='headline'>{$years} years of experience</p>" : '';
        $aboutHtml     = $bio      ? "<div class='section'><div class='section-title'>About</div><div class='summary'>{$bio}</div></div>" : '';
        $serverHost    = $_SERVER['HTTP_HOST'] ?? 'tatujobhub.xo.je';
        $generatedDate = date('F Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$name} — Resume</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    font-size: 13.5px;
    color: #1e293b;
    background: #f8fafc;
    line-height: 1.6;
  }

  .page {
    max-width: 820px;
    margin: 24px auto;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 32px rgba(0,0,0,.1);
  }

  /* Header */
  .header {
    background: linear-gradient(135deg, #1A56DB, #1e1b4b);
    color: #fff;
    padding: 40px 48px 32px;
  }
  .header h1 { font-size: 2rem; font-weight: 700; letter-spacing: -.5px; margin-bottom: 6px; }
  .header .headline { font-size: 15px; opacity: .85; margin-bottom: 16px; }
  .contact-row { display: flex; flex-wrap: wrap; gap: 12px 24px; font-size: 12.5px; opacity: .9; }

  /* Body */
  .body { padding: 40px 48px; }

  /* Summary */
  .summary { color: #374151; margin-bottom: 28px; line-height: 1.75; }

  /* Section */
  .section { margin-bottom: 28px; }
  .section-title {
    font-size: 11px; font-weight: 700; letter-spacing: 1.5px;
    text-transform: uppercase; color: #1A56DB;
    border-bottom: 2px solid #EBF5FF; padding-bottom: 6px; margin-bottom: 16px;
  }

  /* Skills */
  .skills { display: flex; flex-wrap: wrap; gap: 8px; }
  .skill-tag {
    background: #EBF5FF; color: #1A56DB; border-radius: 50px;
    padding: 5px 14px; font-size: 12px; font-weight: 500;
  }
  .skill-tag em { opacity: .7; font-style: normal; font-size: 11px; }

  /* Items */
  .item { margin-bottom: 18px; }
  .item:last-child { margin-bottom: 0; }
  .item-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
  .item-title { font-weight: 700; font-size: 14px; }
  .item-date { font-size: 12px; color: #64748b; white-space: nowrap; }
  .item-sub { color: #1A56DB; font-size: 12.5px; font-weight: 500; margin-top: 2px; }
  .item-desc { color: #64748b; font-size: 12.5px; margin-top: 6px; }

  /* Footer */
  .resume-footer {
    text-align: center; padding: 16px;
    background: #f8fafc; border-top: 1px solid #e2e8f0;
    font-size: 11px; color: #94a3b8;
  }

  /* Print styles */
  @media print {
    body { background: #fff; }
    .page { margin: 0; border-radius: 0; box-shadow: none; }
    .no-print { display: none !important; }
  }
</style>
</head>
<body>

<!-- Print button (hidden when printing) -->
<div class="no-print" style="text-align:center;padding:16px 0;">
  <button onclick="window.print()"
          style="background:#1A56DB;color:#fff;border:none;padding:10px 28px;
                 border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
    🖨 Print / Save as PDF
  </button>
  <a href="javascript:history.back()"
     style="margin-left:12px;color:#64748b;font-size:13px;">← Back</a>
  <p style="color:#64748b;font-size:12px;margin-top:8px;">
    To save as PDF: Click Print → change Destination to "Save as PDF"
  </p>
</div>

<div class="page">
  <!-- Header -->
  <div class="header">
    <h1>{$name}</h1>
    {$headlineHtml}
    {$yearsHtml}
    <div class="contact-row">{$contactHtml}</div>
  </div>

  <!-- Body -->
  <div class="body">
    {$aboutHtml}
    {$skillsHtml}
    {$expHtml}
    {$eduHtml}
  </div>

  <div class="resume-footer">
    Generated by {$serverHost} · {$generatedDate}
  </div>
</div>

</body>
</html>
HTML;
    }
}
