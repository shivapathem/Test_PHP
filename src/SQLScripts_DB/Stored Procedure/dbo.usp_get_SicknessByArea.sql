USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_SicknessByArea]    Script Date: 24/10/2025 14:54:10 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER         PROCEDURE [dbo].[usp_get_SicknessByArea]
	-- Add the parameters for the stored procedure here
	@divisionID INT = 0,
    @Start_Date DATE,
    @End_Date DATE
AS
BEGIN

DROP TABLE IF EXISTS #TempLeave;

  select dv.DivisionName as Area,
         UD_DisplayFirstName as "Display Forename",
		 UD_DisplayLastName as "Display Surname",
		 UD_StaffNumber as "Staff Number",
		 UD_NetLogin as "Network Login",
		 st.schedulingTeamName as "Home Scheduling Team",
		 td.dDateTime as "Date of Sickness",
		 case when td.ixDayInWeek = 0 then 'Saturday'
		      when td.ixDayInWeek = 1 then 'Sunday'
			  when td.ixDayInWeek = 2 then 'Monday'
			  when td.ixDayInWeek = 3 then 'Tuesday'
			  when td.ixDayInWeek = 4 then 'Wednesday'
			  when td.ixDayInWeek = 5 then 'Thursday'
			  when td.ixDayInWeek = 6 then 'Friday'
		       end as "Day of the Week",
		 CASE WHEN ASP_LeaveType = 3 THEN 'Sick'
			  WHEN ASP_LeaveType = 4 THEN 'U-Sick'
			  WHEN ASP_LeaveType = 5 THEN '-Sick' END as "Name of Sickness",
		 round(cast(isnull(ASP_LeaveDuration,0) as float) / cast(3600 as float),2) as "Duration of Sickness",
		 UD_UserID ScheduledPersonID,
		 ASP_LeaveDuration Duration,
		 0 IsExcludeDuty 
		 into #TempLeave
	from Allocations AL
   inner join TimeDimension TD ON TD.ixYearWeek = AL_WeekNumber 
   inner join AllocationsScheduledPersons asp on AL_AllocationsID = ASP_AllocationsID
											 and td.ixDayInWeek = ASP_iDay
   inner join AllocationsDuties ad on AD_AllocationsDutyID = ASP_AllocationsDutyID
   inner join UserDetails sp on sp.UD_UserID = ASP_SchedulingPersonID
   inner join schedulingTeams st on st.schedulingTeamId = AL_SchedulingTeamID
   inner join Divisions dv on dv.DivisionID = st.divisionid
   where td.dDateTime between @Start_Date and @End_Date
	 AND dv.DivisionID = @divisionID
	 AND AD_DutyType = 8
	 AND ASP_LeaveType IN ( 3,4,5)
order by st.schedulingTeamName, sp.UD_DisplayFirstName, td.dDateTime


  select dv.DivisionName as Area,
		 st.schedulingTeamName as "Home Scheduling Team",	
		 sum(ISNULL(AD_Duration,0)) as "Duration of Duty"
		 into #TempDuty
	from Allocations AL
   inner join TimeDimension TD ON TD.ixYearWeek = AL_WeekNumber 
   inner join AllocationsScheduledPersons asp on AL_AllocationsID = ASP_AllocationsID
											 and td.ixDayInWeek = ASP_iDay
   inner join AllocationsDuties ad on AD_AllocationsDutyID = ASP_AllocationsDutyID
   inner join UserDetails sp on sp.UD_UserID = ASP_SchedulingPersonID
   inner join schedulingTeams st on st.schedulingTeamId = AL_SchedulingTeamID
   inner join Divisions dv on dv.DivisionID = st.divisionid
   where td.dDateTime between @Start_Date and @End_Date
	 AND dv.DivisionID = @divisionID
	 AND AD_DutyType < 7
   group by dv.DivisionName,
		 st.schedulingTeamName
   order by st.schedulingTeamName

select Area,[Display Forename],[Display Surname],[Staff Number],[Network Login],
       [Home Scheduling Team],[Date of Sickness],[Day of the Week],[Name of Sickness],
	   [Duration of Sickness]
 from #TempLeave where IsExcludeDuty = 0 order by 6, 2,7

select cast(count(distinct ScheduledPersonID ) as varchar) from #TempLeave where duration > 0 and IsExcludeDuty = 0
union all
/*select CAST( isnull(TotalDuration,0) / 3600 AS varchar(10)) + ':' 
		+ right('0' + CAST( (isnull(TotalDuration,0) % 3600)/60 AS varchar(2)),2) 
from  ( select sum(isnull(duration,0)) as TotalDuration
	        from #TempLeave ) FD */
select round(CAST( isnull(TotalDuration,0) as float) / cast(3600 as float),2)
from  ( select sum(isnull(duration,0)) as TotalDuration
	        from #TempLeave where IsExcludeDuty = 0 ) FD
union all
select cast(count(1)  as varchar) from #TempLeave where IsExcludeDuty = 0
union all
select cast(count(1)  as varchar) from #TempLeave where duration > 0 and IsExcludeDuty = 0
union all
select cast(count(1)  as varchar) from #TempLeave where [Day of the Week]='Monday' and IsExcludeDuty = 0
union all
select cast(count(1)  as varchar) from #TempLeave where [Day of the Week]='Tuesday' and IsExcludeDuty = 0
union all
select cast(count(1)  as varchar) from #TempLeave where [Day of the Week]='Wednesday' and IsExcludeDuty = 0
union all
select cast(count(1)  as varchar) from #TempLeave where [Day of the Week]='Thursday' and IsExcludeDuty = 0
union all
select cast(count(1)  as varchar) from #TempLeave where [Day of the Week]='Friday' and IsExcludeDuty = 0
union all
select cast(count(1)  as varchar) from #TempLeave where [Day of the Week]='Saturday' and IsExcludeDuty = 0
union all
select cast(count(1)  as varchar) from #TempLeave where [Day of the Week]='Sunday' and IsExcludeDuty = 0



   
BEGIN
	  DECLARE @TempSickSummaryList TABLE  
	         (
			   ScheduledPersonID INT,
			   startdate  DATE,
			   enddate DATE,
			   TotalDuration INT
	          );

	  DECLARE @sickdate date, 
	          @prevsickdate date, 
			  @duration int, 
			  @PrevDuration int,
			  @totalduration int = 0,
			  @scheduledpersonid INT,
			  @prevScheduledPersonID INT,
			  @Cntr INT = 0,
			  @startdate DATE,
			  @InsertFlag BIT;

     DECLARE SickList CURSOR FOR 
      select ScheduledPersonID, 
	         [Date of Sickness],
			 Duration
	    from #TempLeave
		where IsExcludeDuty = 0
		order by 1,2;

      OPEN SickList

      FETCH NEXT FROM SickList INTO 
	              @scheduledpersonid,
				  @sickdate, 
				  @duration

      WHILE @@FETCH_STATUS = 0
        BEGIN

		  IF ( DATEDIFF(DAY,@prevsickdate,@sickdate) = 1
		       AND @prevScheduledPersonID = @scheduledpersonid  )
		   begin
		     set @totalduration = @totalduration + @PrevDuration
			 set @Cntr = @Cntr + 1
			 
			  if ( @Cntr = 1 )
			   begin
			     set @startdate = @prevsickdate
				 set @InsertFlag = 1
			   end

		   end
		  ELSE 
		   begin
			 set @Cntr = 0			 
		   End

		   IF ( @InsertFlag = 1 and @Cntr = 0 )
		    BEGIN
			  set @totalduration = @totalduration + @PrevDuration
			  INSERT INTO @TempSickSummaryList
			  VALUES ( @prevScheduledPersonID, @startdate, @prevsickdate, @totalduration)
			  set @InsertFlag = 0 
		      set @totalduration = 0
			END

		  SET @prevsickdate = @sickdate
		  SET @prevScheduledPersonID = @scheduledpersonid
		  SET @PrevDuration = @duration

		  FETCH NEXT FROM SickList INTO 
		              @scheduledpersonid,
					  @sickdate, 
					  @duration

        END

      CLOSE SickList;

      DEALLOCATE SickList; 

	  select area,
		    [Display Forename],
			[Display Surname],
			[Staff Number],
			[Network Login],
			[Home Scheduling Team],
			tsl.startdate SickStartDate,
			tsl.enddate SickEndDate,
			'Consecutive' as [Name of Sickness],
			cast(tsl.TotalDuration as float) / cast(3600 as float) as TotalDuration
	    from @TempSickSummaryList TSL 
		inner join  ( select distinct area,
		                     [Display Forename],
							 [Display Surname],
							 [Staff Number],
							 [Network Login],
							 [Home Scheduling Team],
		                     ScheduledPersonID  
						from #TempLeave
					   where IsExcludeDuty = 0
					) TL on tsl.ScheduledPersonID = tl.ScheduledPersonID
	   order by 1,2,7

	    select td.Area,
		       td."Home Scheduling Team",	
               round(cast(isnull(td."Duration of Duty",0) as float) / cast(3600 as float),2) as "Duration of Duty",
               round(cast(isnull(fd.SickDuration,0) as float) / cast(3600 as float),2) as SickDuration
	      from #TempDuty TD
		  inner join ( select Area,
		                      "Home Scheduling Team",
							  sum(Duration) SickDuration
		                 from #TempLeave 
						 where IsExcludeDuty = 0 
						 group by Area,
		                      "Home Scheduling Team" ) fd on fd.Area =td.Area and fd.[Home Scheduling Team] = td.[Home Scheduling Team]

END;

END