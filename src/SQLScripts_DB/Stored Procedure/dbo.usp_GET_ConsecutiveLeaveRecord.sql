USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_ConsecutiveLeaveRecord]    Script Date: 28/06/2022 15:41:52 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE [dbo].[usp_GET_ConsecutiveLeaveRecord]
@leavedatevar VARCHAR(40),
@schedulingpersonid INT
AS
BEGIN

	SET NOCOUNT ON;
	DECLARE @CounterNext INT ,@CounterPrev INT , @trigger varchar(10),@leavedate datetime,@currentDate INT
	DECLARE @leaveStartDate date,@leaveEndDate date,@leaveyear int,@leaveyearstartdate date,@leaveyearenddate date,@nextleaveyear int
SET @CounterNext=0
SET @CounterPrev=0
SET @currentDate = 1
SET @trigger = 'on'
SET @leavedate = @leavedatevar
SET @leaveyear =YEAR(DATEADD(month, -3, @leavedate))

SET @nextleaveyear = @leaveyear+1
set @leaveyearstartdate = DATEFROMPARTS(@leaveyear,04,01)
set @leaveyearenddate = DATEFROMPARTS(@nextleaveyear,03,31)

--GET NEXT DAY SICKNESS
set @leaveEndDate = @leavedate
WHILE ( @trigger = 'on')
BEGIN
	
		if @CounterNext = 0
		begin
			set @leavedate =  CAST(DATEADD(day,1,@leavedate) AS date)
			
		end
		
         IF EXISTS (SELECT 1 FROM LeaveApplications (nolock) where dDate =@leavedate and SchedulingPersonID = @schedulingpersonid and Deleted = 0)
			BEGIN
			 
			
			IF @leavedate <= @leaveyearenddate
			BEGIN
			SET @CounterNext  = @CounterNext  + 1 
			set @leaveEndDate = @leavedate
			set @leavedate = CAST(DATEADD(day,1,@leavedate) AS date)
			END
			ELSE
			BEGIN
				SET @trigger= 'off'
				
			END
			END
		ELSE 
			BEGIN
				SET @trigger= 'off'
			END
		

END
--END NEXT DAY WHILE LOOP

--GET PREV DAY SICKNESS
SET @trigger = 'on'
SET @leavedate = @leavedatevar
set @leaveStartDate = @leavedate
WHILE ( @trigger = 'on')
BEGIN
		if @CounterPrev = 0
		begin
			set @leavedate =  CAST(DATEADD(day,-1,@leavedate) AS date)
			
		end
		
         IF EXISTS (SELECT 1 FROM LeaveApplications (nolock) where dDate =@leavedate and SchedulingPersonID = @schedulingpersonid and Deleted= 0)
			BEGIN
			 
			 
					 IF @leavedate >= @leaveyearstartdate
					 BEGIN
						SET @CounterPrev  = @CounterPrev  + 1 
						set @leaveStartDate = @leavedate
						set @leavedate =CAST(DATEADD(day,-1,@leavedate) AS date)
					END
					ELSE
			BEGIN
				SET @trigger= 'off'
				
			END
					
			END
		ELSE 
			BEGIN
				SET @trigger= 'off'
			END

END
--END PREV DAY WHILE L00P
SELECT @CounterPrev PrevDay,@CounterNext NextDay,@CounterPrev + @CounterNext+ @currentDate totalcount,@leaveStartDate leavestartdate,@leaveEndDate leaveenddate

						
END

