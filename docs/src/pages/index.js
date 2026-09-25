import React from 'react';
import {Redirect} from '@docusaurus/router';

/**
 * Getting started moved from `/` to `/getting-started` when the docs switched
 * to the Package docs front matter, whose slugs are relative.
 */
export default function Home() {
  return <Redirect to="/getting-started" />;
}
