/* -*- c++ -*- */

/*
 * Copyright 2004,2006-2011,2013 Free Software Foundation, Inc.
 *
 * This file is part of GNU Radio
 *
 * GNU Radio is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3, or (at your option)
 * any later version.
 *
 * GNU Radio is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GNU Radio; see the file COPYING.  If not, write to
 * the Free Software Foundation, Inc., 51 Franklin Street,
 * Boston, MA 02110-1301, USA.
 */

#ifdef HAVE_CONFIG_H
# include "config.h"
#endif // ifdef HAVE_CONFIG_H

#include "nonstop_wavfile_sink_impl.h"
#include "nonstop_wavfile_sink.h"
#include <gnuradio/io_signature.h>
#include <stdexcept>
#include <climits>
#include <cstring>
#include <cmath>
#include <fcntl.h>
#include <gnuradio/thread/thread.h>
#include <boost/math/special_functions/round.hpp>
#include <stdio.h>

// win32 (mingw/msvc) specific
#ifdef HAVE_IO_H
# include <io.h>
#endif // ifdef HAVE_IO_H
#ifdef O_BINARY
# define OUR_O_BINARY O_BINARY
#else // ifdef O_BINARY
# define OUR_O_BINARY 0
#endif // ifdef O_BINARY

// should be handled via configure
#ifdef O_LARGEFILE
# define OUR_O_LARGEFILE O_LARGEFILE
#else // ifdef O_LARGEFILE
# define OUR_O_LARGEFILE 0
#endif // ifdef O_LARGEFILE

namespace gr {
namespace blocks {
nonstop_wavfile_sink_impl::sptr
nonstop_wavfile_sink_impl::make(int          n_channels,  unsigned int sample_rate, int          bits_per_sample, bool         use_float)
{
  return gnuradio::get_initial_sptr(new nonstop_wavfile_sink_impl( n_channels, sample_rate, bits_per_sample, use_float));
}

nonstop_wavfile_sink_impl::nonstop_wavfile_sink_impl(
                                                     int          n_channels,
                                                     unsigned int sample_rate,
                                                     int          bits_per_sample,
                                                     bool use_float)
  : sync_block("nonstop_wavfile_sink",
               io_signature::make(1, n_channels, (use_float) ? sizeof(float) : sizeof(int16_t)),
               io_signature::make(0, 0, 0)),
  d_sample_rate(sample_rate), d_nchans(n_channels),
  d_use_float(use_float), d_fp(0), d_current_call(NULL)
{
  if ((bits_per_sample != 8) && (bits_per_sample != 16)) {
    throw std::runtime_error("Invalid bits per sample (supports 8 and 16)");
  }
  d_bytes_per_sample = bits_per_sample / 8;
  d_first_work = true;
  d_sample_count = 0;
}

char * nonstop_wavfile_sink_impl::get_filename() {
  return current_filename;
}

bool nonstop_wavfile_sink_impl::open(Call *call) {
  gr::thread::scoped_lock guard(d_mutex);
  d_current_call = call;
  d_conventional = call->is_conventional();
  d_first_work = true;
  d_sample_count = 0;
  
  return true;
}

bool nonstop_wavfile_sink_impl::open_internal(const char *filename) {
  int d_first_sample_pos;
  unsigned d_samples_per_chan;

  // we use the open system call to get access to the O_LARGEFILE flag.
  //  O_APPEND|
  int fd;

  if ((fd = ::open(filename,
                   O_RDWR | O_CREAT | OUR_O_LARGEFILE | OUR_O_BINARY,
                   0664)) < 0) {
    perror(filename);
    BOOST_LOG_TRIVIAL(error) << "wav error opening: " << filename << std::endl;
    return false;
  }

  if (d_fp) { // if we've already got a new one open, close it
    BOOST_LOG_TRIVIAL(trace) << "File pointer already open, closing " << d_fp << " more" << current_filename << " for " << filename << std::endl;

    // fclose(d_fp);
    // d_fp = NULL;
  }

  if (strlen(filename) >= 255) {
    BOOST_LOG_TRIVIAL(error) << "nonstop_wavfile_sink: Error! filename longer than 255";
  } else {
    strcpy(current_filename, filename);
  }

  if ((d_fp = fdopen(fd, "rb+")) == NULL) {
    perror(filename);
    ::close(fd); // don't leak file descriptor if fdopen fails.
    BOOST_LOG_TRIVIAL(error) << "wav open failed" << std::endl;
    return false;
  }

    d_sample_count = 0;

    if (!wavheader_write(d_fp, d_sample_rate, d_nchans, d_bytes_per_sample)) {
      fprintf(stderr, "[%s] could not write to WAV file\n", __FILE__);
      return false;
    }
  

  if (d_bytes_per_sample == 1) {
    d_max_sample_val  = UCHAR_MAX;
    d_min_sample_val  = 0;
    d_normalize_fac   = d_max_sample_val / 2;
    d_normalize_shift = 1;
  }
  else if (d_bytes_per_sample == 2) {
    d_max_sample_val  = SHRT_MAX;
    d_min_sample_val  = SHRT_MIN;
    d_normalize_fac   = d_max_sample_val;
    d_normalize_shift = 0;
  }

  return true;
}


void
nonstop_wavfile_sink_impl::close()
{
  gr::thread::scoped_lock guard(d_mutex);

  if (!d_fp) {
    BOOST_LOG_TRIVIAL(error) << "wav error closing file" << std::endl;
    return;
  }

  close_wav();
  d_current_call = NULL;
}

void
nonstop_wavfile_sink_impl::close_wav()
{
  unsigned int byte_count = d_sample_count * d_bytes_per_sample;

  wavheader_complete(d_fp, byte_count);

  fclose(d_fp);
  d_fp = NULL;

}

nonstop_wavfile_sink_impl::~nonstop_wavfile_sink_impl()
{
  close();
}

bool nonstop_wavfile_sink_impl::stop()
{
  return true;
}

void nonstop_wavfile_sink_impl::log_p25_metadata(long unitId, const char* system_type, bool emergency)
{
  if (d_current_call == NULL) {
    BOOST_LOG_TRIVIAL(debug) << "Unable to log: " << system_type << " : " << unitId << ", no current call.";
  }
  else {
    BOOST_LOG_TRIVIAL(debug) << "Logging " << system_type << " : " << unitId << " to current call.";
    d_current_call->add_signal_source(unitId, system_type, emergency ? SignalType::Emergency : SignalType::Normal);
  }
}



int nonstop_wavfile_sink_impl::work(int noutput_items,  gr_vector_const_void_star& input_items,  gr_vector_void_star& output_items) {
  
  gr::thread::scoped_lock guard(d_mutex); // hold mutex for duration of this

    // if we do not have a valid current_call, dropp everything on the floor.
    if (d_current_call == NULL) {
      return noutput_items;
    }
    
    if (d_first_work ) 
    {
      if (d_fp) {
        BOOST_LOG_TRIVIAL(error) << "Weird - trying to open a file, but already have a FP";
    
      }

        d_current_call->create_filename();
        if (!open_internal(d_current_call->get_filename())) {
            BOOST_LOG_TRIVIAL(error) << "can't open file";
        }
      d_first_work = false;
      d_start_time = time(NULL);
    }

  int nwritten = dowork(noutput_items, input_items, output_items);

  d_stop_time = time(NULL);

  return nwritten;
}

time_t nonstop_wavfile_sink_impl::get_start_time() {
	return d_start_time;
}

time_t nonstop_wavfile_sink_impl::get_stop_time() {
	return d_stop_time;
}


int nonstop_wavfile_sink_impl::dowork(int noutput_items,  gr_vector_const_void_star& input_items,  gr_vector_void_star& output_items) {
  // block

  int n_in_chans = input_items.size();

  short int sample_buf_s;

  int nwritten;

  if (!d_fp) // drop output on the floor
  {
    BOOST_LOG_TRIVIAL(error) << "Wav - Dropping items, no fp: " << noutput_items << " Filename: " << current_filename << " Current sample count: " << d_sample_count << std::endl;
    return noutput_items;
  }
  std::vector<gr::tag_t> tags;
  pmt::pmt_t this_key(pmt::intern("src_id"));
  get_tags_in_range(tags, 0, nitems_read(0), nitems_read(0) + noutput_items);

  unsigned pos = 0;
  //long curr_src_id = 0;

  for (unsigned int i = 0; i < tags.size(); i++) {
    if (pmt::eq(this_key, tags[i].key)) {
      long src_id  = pmt::to_long(tags[i].value);
      pos = d_sample_count + (tags[i].offset - nitems_read(0));
      // double   sec = (double)pos  / (double)d_sample_rate;
      if (curr_src_id != src_id) {
        log_p25_metadata(src_id, d_current_call->get_system_type().c_str(), false);
        // BOOST_LOG_TRIVIAL(info) << " [" << i << "]-[ " << src_id << " : Pos - " << pos << " offset: " << tags[i].offset - nitems_read(0) << " : " << sec << " ] " << std::endl;
        curr_src_id = src_id;
      }
    }
  }

  // std::cout << std::endl;

  for (nwritten = 0; nwritten < noutput_items; nwritten++) {
    for (int chan = 0; chan < d_nchans; chan++) {
      // Write zeros to channels which are in the WAV file
      // but don't have any inputs here
      if (chan < n_in_chans) {
        if (d_use_float) {
          float **in         = (float **)&input_items[0];
          sample_buf_s = convert_to_short(in[chan][nwritten]);
        } else {
          int16_t **in         = (int16_t **)&input_items[0];
        sample_buf_s = in[chan][nwritten];
      }
      }
      else {
        sample_buf_s = 0;
      }

      wav_write_sample(d_fp, sample_buf_s, d_bytes_per_sample);

      if (feof(d_fp) || ferror(d_fp)) {
        fprintf(stderr, "[%s] file i/o error\n", __FILE__);
        close();
        return nwritten;
      }
      d_sample_count++;
    }
  }

  // fflush (d_fp);  // this is added so unbuffered content is written.
  tags.clear();
  return nwritten;
}

short int nonstop_wavfile_sink_impl::convert_to_short(float sample)
{
  sample += d_normalize_shift;
  sample *= d_normalize_fac;

  if (sample > d_max_sample_val) {
    sample = d_max_sample_val;
  }
  else if (sample < d_min_sample_val) {
    sample = d_min_sample_val;
  }

  return (short int)boost::math::iround(sample);
}

void
nonstop_wavfile_sink_impl::set_bits_per_sample(int bits_per_sample)
{
  gr::thread::scoped_lock guard(d_mutex);

  if ((bits_per_sample == 8) || (bits_per_sample == 16)) {
    d_bytes_per_sample = bits_per_sample / 8;
  }
}

void
nonstop_wavfile_sink_impl::set_sample_rate(unsigned int sample_rate)
{
  gr::thread::scoped_lock guard(d_mutex);

  d_sample_rate = sample_rate;
}

int
nonstop_wavfile_sink_impl::bits_per_sample()
{
  return d_bytes_per_sample * 8;
}

unsigned int
nonstop_wavfile_sink_impl::sample_rate()
{
  return d_sample_rate;
}

double
nonstop_wavfile_sink_impl::length_in_seconds()
{
  // std::cout << "Filename: "<< current_filename << "Sample #: " <<
  // d_sample_count << " rate: " << d_sample_rate << " bytes: " <<
  // d_bytes_per_sample << "\n";
  return (double)d_sample_count  / (double)d_sample_rate;

  // return (double) ( d_sample_count * d_bytes_per_sample_new * 8) / (double)
  // d_sample_rate;
}

void
nonstop_wavfile_sink_impl::do_update()
{}
} /* namespace blocks */
} /* namespace gr */
