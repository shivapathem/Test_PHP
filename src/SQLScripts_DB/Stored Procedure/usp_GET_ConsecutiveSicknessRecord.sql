USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_ConsecutiveSicknessRecord]    Script Date: 14/11/2025 18:23:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_GET_ConsecutiveSicknessRecord]
@griddate DATE,
@scheduledpersonid INT
AS
BEGIN

	SET NOCOUNT ON;
	SET DATEFORMAT YMD;

	DECLARE @CounterNext INT ,
			@CounterPrev INT ,
			@trigger varchar(10),
			@sickdate datetime,
			@currentDate INT;

	DECLARE @sickStartDate date,
			@sickEndDate date;

	DECLARE @SicknessTable TABLE (SicknessStartDate DATE,
					 SicknessEndDate DATE);

		WITH DateWithRowNum AS (
   			SELECT ddate,
       			   ROW_NUMBER() OVER (ORDER BY ddate) AS RowNum
   			  FROM LeaveApplications
			 WHERE SchedulingPersonID = @scheduledpersonid
			   AND dDate BETWEEN DATEADD(DAY,-90,@griddate) AND DATEADD(DAY,90,@griddate)
			   AND LeaveTypeID in (3,4,5) AND Deleted = 0
			   ),
		DateGroups AS (
   			SELECT ddate,
       			   DATEADD(DAY, -RowNum, ddate) AS GroupID
   			  FROM DateWithRowNum
					  )
		INSERT INTO @SicknessTable
		SELECT MIN(ddate) AS SicknessStartDate,
   			   MAX(ddate) AS SicknessEndDate
		  FROM DateGroups
		 GROUP BY GroupID;

	    SELECT @sickStartDate = SicknessStartDate
          FROM @SicknessTable
	     WHERE DATEADD(DAY,-1,@griddate) BETWEEN SicknessStartDate AND SicknessEndDate;

	    SELECT @sickEndDate = SicknessEndDate
          FROM @SicknessTable
	     WHERE DATEADD(DAY,1,@griddate) BETWEEN SicknessStartDate AND SicknessEndDate;

	   SET @CounterPrev = DATEDIFF(DAY,@sickStartDate,@griddate);

	   SET @CounterNext = DATEDIFF(DAY,@griddate,@sickEndDate);

	   SELECT @CounterPrev PrevDay,
			  @CounterNext NextDay,
			  ISNULL(@CounterPrev,0) + ISNULL(@CounterNext,0)+ 1 totalcount,
			  @sickStartDate sickstartdate,
			  @sickEndDate sickenddate


END