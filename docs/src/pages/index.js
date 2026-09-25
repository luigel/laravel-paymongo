import React from 'react';
import {Redirect} from '@docusaurus/router';

/**
 * The docs start at `/introduction`: the Package docs front matter's slugs
 * are relative, so no page lives at `/`.
 */
export default function Home() {
  return <Redirect to="/introduction" />;
}
